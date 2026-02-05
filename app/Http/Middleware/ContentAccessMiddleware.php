<?php

namespace App\Http\Middleware;

use App\Models\Content;
use App\Models\MediaAsset;
use App\Services\AccessEngine\AccessDecision;
use App\Services\AccessEngine\AccessEngine;
use App\Services\AccessEngine\AccessRequest;
use App\Services\Media\MediaService;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentAccessMiddleware
{
    public function __construct(
        private AccessEngine $accessEngine,
        private MediaService $mediaService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('features.flags.access_engine')) {
            return $next($request);
        }

        $content = $request->route('content');
        if ($content instanceof Content) {
            $decision = $this->accessEngine->decide(
                new AccessRequest(
                    $request->user(),
                    $content->visibility,
                    $content->creator_id,
                    $content->id,
                    $content->required_tier_id,
                    $content->ppv_price_atomic
                )
            );

            $request->attributes->set('access_decision', $decision);

            if ($decision->granted) {
                return $next($request);
            }

            if ($this->isApiRequest($request)) {
                $preview = $this->buildPreview($decision);

                return response()->json([
                    'granted' => false,
                    'reason' => $decision->reason,
                    'preview' => $preview,
                    'cta' => $preview['cta'],
                ], Response::HTTP_FORBIDDEN);
            }

            return $next($request);
        }

        $mediaAsset = $request->route('mediaAsset');
        if ($mediaAsset instanceof MediaAsset) {
            try {
                $this->mediaService->authorizeView($request->user(), $mediaAsset);
            } catch (AuthorizationException $exception) {
                if ($this->isApiRequest($request)) {
                    return response()->json([
                        'granted' => false,
                        'reason' => $exception->getMessage(),
                    ], Response::HTTP_FORBIDDEN);
                }

                throw $exception;
            }
        }

        return $next($request);
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->expectsJson()
            || $request->is('api/*')
            || $request->is('media/*');
    }

    private function buildPreview(AccessDecision $decision): array
    {
        if ($decision->granted) {
            return ['type' => 'none', 'cta' => null];
        }

        $ctaType = match ($decision->reason) {
            'not_logged_in', 'not_verified' => 'register',
            'subscription_required' => 'subscribe',
            'tier_required' => 'upgrade',
            'ppv_required' => 'buy_ppv',
            default => null,
        };

        $cta = $ctaType === null ? null : array_filter([
            'type' => $ctaType,
            'required_tier_level' => $decision->tierHint['required_level'] ?? null,
            'required_tier_name' => $decision->tierHint['required_name'] ?? null,
            'endpoint' => $ctaType === 'upgrade' ? '/api/creators/{username}/tiers/compare' : null,
        ], fn ($value) => $value !== null);

        return ['type' => 'blur', 'cta' => $cta];
    }
}
