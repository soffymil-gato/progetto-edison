import { cn } from '@/lib/cn';
import { useShared } from '@/lib/useShared';

/** Proportions of the official artwork (viewBox of the SVG files). */
const LOGO_WIDTH = 783;
const LOGO_HEIGHT = 252;

/**
 * Official Edison logo (EDISON_LOGO_PATH), in its negative version on dark
 * backgrounds (EDISON_LOGO_NEGATIVE_PATH). Without it a neutral, clearly
 * marked placeholder: the brand is never redrawn or imitated.
 *
 * Size it through the height (`className="h-12"`); the width follows.
 */
export function BrandMark({ tone = 'dark', className }: { tone?: 'dark' | 'light'; className?: string }) {
    const { brand, appName } = useShared();
    const src = tone === 'light' ? (brand.logoNegativeUrl ?? brand.logoUrl) : brand.logoUrl;

    if (src) {
        return (
            <img
                src={src}
                alt={appName}
                width={LOGO_WIDTH}
                height={LOGO_HEIGHT}
                className={cn('block h-10 w-auto max-w-none', className)}
            />
        );
    }

    return (
        <span
            role="img"
            className={cn('inline-flex items-center gap-2.5', className)}
            aria-label={`${appName} — logo ufficiale da fornire`}
        >
            <span
                className={cn(
                    'inline-flex h-8 items-center rounded-lg border border-dashed px-2 text-[0.625rem] font-semibold tracking-[0.16em] uppercase',
                    tone === 'dark' ? 'border-brand-300 text-brand-700' : 'border-white/40 text-white/80',
                )}
                aria-hidden
            >
                Logo
            </span>
            <span
                className={cn(
                    'font-display text-lg font-semibold tracking-tight',
                    tone === 'dark' ? 'text-ink' : 'text-white',
                )}
                aria-hidden
            >
                {appName}
            </span>
        </span>
    );
}
