import { type ReactNode, useEffect, useRef, useState } from 'react';

import { cn } from '@/lib/cn';

/**
 * Fades content in when it enters the viewport. Content is visible by default
 * (no-JS, reduced motion): the animation is only a progressive enhancement.
 */
export function Reveal({ children, className, delay = 0, as: Tag = 'div' }: { children: ReactNode; className?: string; delay?: number; as?: 'div' | 'li' | 'section' }) {
    const ref = useRef<HTMLElement | null>(null);
    const [state, setState] = useState<'idle' | 'hidden' | 'shown'>('idle');

    useEffect(() => {
        const node = ref.current;
        if (!node || window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) return;
        const rect = node.getBoundingClientRect();
        if (rect.top < window.innerHeight) return;
        setState('hidden');
        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry?.isIntersecting) {
                    setState('shown');
                    observer.disconnect();
                }
            },
            { rootMargin: '0px 0px -10% 0px' },
        );
        observer.observe(node);
        return () => observer.disconnect();
    }, []);

    return (
        <Tag
            ref={ref as never}
            className={cn(state === 'hidden' && 'opacity-0', state === 'shown' && 'animate-fade-up', className)}
            style={state === 'shown' && delay > 0 ? { animationDelay: `${delay}ms` } : undefined}
        >
            {children}
        </Tag>
    );
}
