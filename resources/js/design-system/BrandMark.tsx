import { cn } from '@/lib/cn';
import { useShared } from '@/lib/useShared';

/**
 * Official Edison logo when supplied (EDISON_LOGO_PATH). Until then a neutral,
 * clearly marked placeholder: the brand is never redrawn or imitated.
 */
export function BrandMark({ tone = 'dark', className }: { tone?: 'dark' | 'light'; className?: string }) {
    const { brand, appName } = useShared();

    if (brand.logoUrl) {
        return <img src={brand.logoUrl} alt={appName} className={cn('h-8 w-auto', className)} />;
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
