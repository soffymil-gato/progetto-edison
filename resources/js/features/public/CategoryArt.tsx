import { cn } from '@/lib/cn';

/**
 * Abstract artwork used until Edison supplies licensed product photography.
 * It is explicitly labelled as an image placeholder.
 */
export function CategoryArt({ index, className }: { index: number; className?: string }) {
    const variants = [
        'bg-[radial-gradient(circle_at_20%_20%,#4f93ff_0,transparent_45%),radial-gradient(circle_at_85%_80%,#3cc8ff_0,transparent_40%),linear-gradient(135deg,#0d2a66,#071a3d)]',
        'bg-[radial-gradient(circle_at_80%_25%,#86b8ff_0,transparent_42%),radial-gradient(circle_at_15%_85%,#2a6fff_0,transparent_45%),linear-gradient(160deg,#11398f,#040f26)]',
        'bg-[radial-gradient(circle_at_50%_0%,#3cc8ff_0,transparent_50%),radial-gradient(circle_at_0%_100%,#1656e0_0,transparent_45%),linear-gradient(200deg,#0d2a66,#071a3d)]',
    ];
    return (
        <div className={cn('relative overflow-hidden', variants[index % variants.length], className)} role="img" aria-label="Immagine della categoria da fornire">
            <div className="absolute inset-0 [background-image:linear-gradient(rgb(255_255_255/0.07)_1px,transparent_1px),linear-gradient(90deg,rgb(255_255_255/0.07)_1px,transparent_1px)] [background-size:28px_28px]" aria-hidden />
            <svg className="absolute inset-0 h-full w-full" viewBox="0 0 400 240" preserveAspectRatio="none" aria-hidden>
                <path d={['M0 180 C 120 150, 200 60, 400 40', 'M0 60 C 140 90, 240 200, 400 190', 'M0 120 C 100 40, 300 200, 400 100'][index % 3]} fill="none" stroke="rgb(143 227 255 / 0.55)" strokeWidth="1.5" />
            </svg>
            <span className="absolute right-3 bottom-3 rounded-md bg-black/30 px-2 py-1 text-[0.625rem] font-semibold tracking-[0.14em] text-white/85 uppercase backdrop-blur">Immagine da fornire</span>
        </div>
    );
}
