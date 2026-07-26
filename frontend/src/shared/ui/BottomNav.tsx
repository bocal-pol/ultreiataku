import { NavLink } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

interface NavItem {
  to: string;
  labelKey: string;
  icon: React.ReactNode;
}

const MapIcon = () => (
  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/>
    <line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/>
  </svg>
);
const ListIcon = () => (
  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
    <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
  </svg>
);
const PackIcon = () => (
  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="M16 6l2 2H6l2-2"/><path d="M6 8v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V8"/><path d="M10 6V4a2 2 0 0 1 4 0v2"/>
  </svg>
);
const BookIcon = () => (
  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
  </svg>
);
const UserIcon = () => (
  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
  </svg>
);

export function BottomNav() {
  const { t } = useTranslation('pilgrimage');

  const items: NavItem[] = [
    { to: '/carte', labelKey: 'nav.map', icon: <MapIcon /> },
    { to: '/belgique', labelKey: 'nav.stages', icon: <ListIcon /> },
    { to: '/sac', labelKey: 'nav.pack', icon: <PackIcon /> },
    { to: '/journal', labelKey: 'nav.journal', icon: <BookIcon /> },
    { to: '/profil', labelKey: 'nav.profile', icon: <UserIcon /> },
  ];

  return (
    <nav
      aria-label={t('nav.stages')}
      style={{
        position: 'fixed',
        bottom: 0,
        left: 0,
        right: 0,
        height: 'calc(var(--nav-height) + var(--nav-safe-area))',
        paddingBottom: 'var(--nav-safe-area)',
        backgroundColor: 'var(--nav-bg)',
        borderTop: 'var(--nav-border-top)',
        display: 'flex',
        zIndex: 1000,
      }}
    >
      {items.map(({ to, labelKey, icon }) => (
        <NavLink
          key={to}
          to={to}
          style={({ isActive }) => ({
            flex: 1,
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            justifyContent: 'center',
            gap: '2px',
            minWidth: 'var(--nav-item-min-width)',
            color: isActive ? 'var(--nav-item-active-color)' : 'var(--nav-item-inactive-color)',
            textDecoration: 'none',
            fontSize: '10px',
            fontWeight: isActive ? 'var(--font-weight-medium)' : 'var(--font-weight-regular)',
            padding: '8px 4px',
            WebkitTapHighlightColor: 'transparent',
          })}
          aria-current={undefined}
        >
          {({ isActive }) => (
            <>
              {icon}
              <span>{t(labelKey)}</span>
              {isActive && (
                <span
                  aria-hidden="true"
                  style={{
                    position: 'absolute',
                    bottom: 'calc(var(--nav-safe-area) + 2px)',
                    width: '24px',
                    height: '2px',
                    backgroundColor: 'var(--nav-item-active-color)',
                    borderRadius: 'var(--radius-full)',
                  }}
                />
              )}
            </>
          )}
        </NavLink>
      ))}
    </nav>
  );
}
