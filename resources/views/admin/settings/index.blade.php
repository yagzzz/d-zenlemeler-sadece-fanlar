@extends('admin.layout')

@section('content')
<h1 style="margin-bottom:1.5rem;">Site Ayarları</h1>

<form method="POST" action="/admin/settings">
    @csrf
    @method('PUT')

    @foreach ($settings as $group => $items)
        <div class="admin-card group-section">
            <h3>
                @switch($group)
                    @case('general') Genel Ayarlar @break
                    @case('ads') Reklam Alanları @break
                    @case('features') Özellik Ayarları @break
                    @default {{ ucfirst($group) }}
                @endswitch
            </h3>

            @foreach ($items as $setting)
                <div class="form-group">
                    <label for="setting-{{ $setting->key }}">
                        {{ $setting->description ?? $setting->key }}
                    </label>

                    @if ($setting->type === 'boolean')
                        <div>
                            <label style="font-weight:normal; cursor:pointer;">
                                <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                <input type="checkbox"
                                       name="settings[{{ $setting->key }}]"
                                       value="1"
                                       id="setting-{{ $setting->key }}"
                                       {{ $setting->typed_value ? 'checked' : '' }}>
                                Aktif
                            </label>
                        </div>
                    @elseif ($setting->type === 'text')
                        <textarea name="settings[{{ $setting->key }}]"
                                  id="setting-{{ $setting->key }}">{{ $setting->value }}</textarea>
                    @else
                        <input type="text"
                               name="settings[{{ $setting->key }}]"
                               id="setting-{{ $setting->key }}"
                               value="{{ $setting->value }}">
                    @endif

                    <div class="hint">Anahtar: {{ $setting->key }}</div>
                </div>
            @endforeach
        </div>
    @endforeach

    <button type="submit" class="btn-sm btn-primary" style="padding:0.5rem 1.5rem; font-size:1rem;">
        💾 Kaydet
    </button>
</form>
@endsection
