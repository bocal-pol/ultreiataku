/**
 * PhotoUploader — Sélection photo (galerie/caméra), preview, alt-text, keep_location
 * ULTREIA-52 — Vague 1e
 */

import { useRef, useState, useId } from 'react';
import { useTranslation } from 'react-i18next';

export interface PhotoUploadValue {
  file: File;
  previewUrl: string;
  altText: string;
  keepLocation: boolean;
}

interface PhotoUploaderProps {
  value: PhotoUploadValue | null;
  onChange: (value: PhotoUploadValue | null) => void;
}

export function PhotoUploader({ value, onChange }: PhotoUploaderProps) {
  const { t } = useTranslation('pilgrimage');
  const inputRef = useRef<HTMLInputElement>(null);
  const [altText, setAltText] = useState(value?.altText ?? '');
  const [keepLocation, setKeepLocation] = useState(value?.keepLocation ?? false);
  const altId = useId();
  const keepLocId = useId();

  function handleFileChange(e: React.ChangeEvent<HTMLInputElement>) {
    const file = e.target.files?.[0];
    if (!file) return;
    const previewUrl = URL.createObjectURL(file);
    onChange({ file, previewUrl, altText, keepLocation });
  }

  function handleAltChange(e: React.ChangeEvent<HTMLInputElement>) {
    setAltText(e.target.value);
    if (value) {
      onChange({ ...value, altText: e.target.value });
    }
  }

  function handleKeepLocationChange(e: React.ChangeEvent<HTMLInputElement>) {
    setKeepLocation(e.target.checked);
    if (value) {
      onChange({ ...value, keepLocation: e.target.checked });
    }
  }

  function handleRemove() {
    if (value) URL.revokeObjectURL(value.previewUrl);
    onChange(null);
    if (inputRef.current) inputRef.current.value = '';
    setAltText('');
    setKeepLocation(false);
  }

  return (
    <div
      data-testid="photo-uploader"
      style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-3)' }}
    >
      {/* Bouton sélection */}
      {!value && (
        <button
          type="button"
          onClick={() => inputRef.current?.click()}
          style={{
            minHeight: '56px',
            backgroundColor: 'var(--color-bg-elevated)',
            border: '1px dashed var(--color-border-subtle)',
            borderRadius: 'var(--radius-lg)',
            color: 'var(--color-text-accent)',
            fontSize: 'var(--font-size-sm)',
            cursor: 'pointer',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            gap: 'var(--space-2)',
          }}
        >
          <span aria-hidden="true">📷</span>
          Ajouter une photo
        </button>
      )}

      {/* Input masqué — capture galerie et caméra */}
      <input
        ref={inputRef}
        type="file"
        accept="image/*"
        capture="environment"
        onChange={handleFileChange}
        style={{ display: 'none' }}
        aria-hidden="true"
      />

      {/* Preview + champs */}
      {value && (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-3)' }}>
          {/* Preview */}
          <div style={{ position: 'relative' }}>
            <img
              data-testid="photo-preview"
              src={value.previewUrl}
              alt={altText || 'Preview'}
              style={{
                width: '100%',
                maxHeight: '200px',
                objectFit: 'cover',
                borderRadius: 'var(--radius-lg)',
                border: '1px solid var(--color-border-subtle)',
              }}
            />
            <button
              type="button"
              onClick={handleRemove}
              aria-label="Supprimer la photo"
              style={{
                position: 'absolute',
                top: 'var(--space-2)',
                right: 'var(--space-2)',
                backgroundColor: 'rgba(0,0,0,0.6)',
                color: 'white',
                border: 'none',
                borderRadius: '50%',
                width: '32px',
                height: '32px',
                cursor: 'pointer',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: '14px',
              }}
            >
              ✕
            </button>
          </div>

          {/* Alt-text */}
          <div style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-1)' }}>
            <label
              htmlFor={altId}
              style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)' }}
            >
              Description de la photo (accessibilité)
            </label>
            <input
              id={altId}
              type="text"
              value={altText}
              onChange={handleAltChange}
              placeholder="Ex: Vue depuis le sommet de la colline"
              style={{
                minHeight: '44px',
                backgroundColor: 'var(--color-bg-base)',
                border: '1px solid var(--color-border-subtle)',
                borderRadius: 'var(--radius-md)',
                padding: '0 var(--space-3)',
                fontSize: 'var(--font-size-sm)',
                color: 'var(--color-text-primary)',
                boxSizing: 'border-box',
                width: '100%',
              }}
            />
          </div>

          {/* Keep location */}
          <div style={{ display: 'flex', alignItems: 'flex-start', gap: 'var(--space-3)' }}>
            <input
              id={keepLocId}
              type="checkbox"
              checked={keepLocation}
              onChange={handleKeepLocationChange}
              style={{ marginTop: '2px', width: '18px', height: '18px', flexShrink: 0 }}
            />
            <label htmlFor={keepLocId} style={{ fontSize: 'var(--font-size-sm)', color: 'var(--color-text-secondary)', lineHeight: '1.4' }}>
              Conserver les données de localisation dans la photo
              <span style={{ display: 'block', fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)', marginTop: '2px' }}>
                Par défaut, les données GPS sont retirées de la photo pour protéger votre vie privée.
              </span>
            </label>
          </div>
        </div>
      )}

      {/* Note EXIF */}
      {!value && (
        <p style={{ margin: 0, fontSize: 'var(--font-size-xs)', color: 'var(--color-text-tertiary)' }}>
          {t('journal.offline_notice')}
        </p>
      )}
    </div>
  );
}
