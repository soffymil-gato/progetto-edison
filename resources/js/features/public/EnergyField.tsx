import { cn } from '@/lib/cn';

/**
 * Original abstract visual for the energy sector: a perspective grid with
 * light pulses travelling along power lines. Pure SVG/CSS (no images, no
 * third-party assets); animations stop with prefers-reduced-motion.
 */
export function EnergyField({ className }: { className?: string }) {
    const lines = [
        'M-40 520 C 180 470, 300 360, 520 330 S 860 250, 1040 150',
        'M-40 600 C 220 560, 380 470, 600 430 S 900 360, 1040 300',
        'M-40 420 C 160 380, 280 260, 470 220 S 780 130, 1040 40',
        'M-40 690 C 260 650, 460 580, 680 540 S 940 480, 1040 450',
    ];

    return (
        <svg className={cn('h-full w-full', className)} viewBox="0 0 1000 700" preserveAspectRatio="xMidYMid slice" aria-hidden focusable="false">
            <defs>
                <radialGradient id="ef-glow" cx="78%" cy="18%" r="70%">
                    <stop offset="0%" stopColor="#2a6fff" stopOpacity="0.55" />
                    <stop offset="45%" stopColor="#1244b4" stopOpacity="0.25" />
                    <stop offset="100%" stopColor="#071a3d" stopOpacity="0" />
                </radialGradient>
                <linearGradient id="ef-line" x1="0" x2="1" y1="0" y2="0">
                    <stop offset="0%" stopColor="#86b8ff" stopOpacity="0" />
                    <stop offset="55%" stopColor="#86b8ff" stopOpacity="0.45" />
                    <stop offset="100%" stopColor="#3cc8ff" stopOpacity="0.9" />
                </linearGradient>
                <linearGradient id="ef-pulse" x1="0" x2="1">
                    <stop offset="0%" stopColor="#3cc8ff" stopOpacity="0" />
                    <stop offset="70%" stopColor="#8fe3ff" stopOpacity="1" />
                    <stop offset="100%" stopColor="#ffffff" stopOpacity="1" />
                </linearGradient>
                <pattern id="ef-grid" width="48" height="48" patternUnits="userSpaceOnUse">
                    <path d="M48 0H0V48" fill="none" stroke="#86b8ff" strokeOpacity="0.08" strokeWidth="1" />
                </pattern>
                <filter id="ef-blur" x="-20%" y="-20%" width="140%" height="140%">
                    <feGaussianBlur stdDeviation="3" />
                </filter>
            </defs>

            <rect width="1000" height="700" fill="#071a3d" />
            <rect width="1000" height="700" fill="url(#ef-grid)" />
            <rect width="1000" height="700" fill="url(#ef-glow)" />

            {/* Concentric arcs */}
            <g fill="none" stroke="#86b8ff" strokeOpacity="0.12">
                {[120, 200, 290, 390].map((r) => (
                    <circle key={r} cx="800" cy="130" r={r} />
                ))}
            </g>

            {/* Power lines */}
            <g fill="none" strokeLinecap="round">
                {lines.map((d, i) => (
                    <path key={`l-${i}`} d={d} stroke="url(#ef-line)" strokeWidth={i === 0 ? 1.6 : 1} />
                ))}
            </g>

            {/* Travelling pulses */}
            <g fill="none" strokeLinecap="round" className="ef-pulses">
                {lines.map((d, i) => (
                    <path
                        key={`p-${i}`}
                        d={d}
                        stroke="url(#ef-pulse)"
                        strokeWidth={i === 0 ? 3 : 2}
                        pathLength={1000}
                        strokeDasharray="90 910"
                        filter="url(#ef-blur)"
                        style={{ animation: `ef-flow ${6 + i * 1.7}s linear ${i * 0.9}s infinite` }}
                    />
                ))}
            </g>

            {/* Nodes */}
            <g>
                {[
                    [520, 330],
                    [600, 430],
                    [470, 220],
                    [680, 540],
                    [860, 238],
                ].map(([x, y], i) => (
                    <g key={`n-${i}`} transform={`translate(${x} ${y})`}>
                        <circle r="10" fill="#2a6fff" fillOpacity="0.18" style={{ animation: `ef-breathe 3.6s ease-in-out ${i * 0.6}s infinite`, transformOrigin: 'center' }} />
                        <circle r="3" fill="#8fe3ff" />
                    </g>
                ))}
            </g>

        </svg>
    );
}
