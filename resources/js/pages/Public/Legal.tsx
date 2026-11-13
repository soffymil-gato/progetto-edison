import { Alert } from '@/design-system/Alert';
import { Txt } from '@/design-system/Placeholder';
import { PageHero, Section } from '@/features/public/Section';
import { PublicLayout, type Seo } from '@/layouts/PublicLayout';

type Cookie = { name: string; purpose: string; duration: string };
type Props = {
    seo: Seo;
    kind: 'privacy' | 'cookie';
    content: {
        title: string;
        notice: string;
        intro?: string;
        sections?: { title: string; text: string }[];
        technical_cookies?: Cookie[];
    };
};

export default function Legal({ seo, kind, content }: Props) {
    return (
        <PublicLayout seo={seo} tone="dark">
            <PageHero eyebrow="Informazioni legali" title={content.title} />
            <Section>
                <div className="mx-auto max-w-3xl">
                    <Alert tone="warning" title="Testo in attesa di validazione">
                        {content.notice}
                    </Alert>

                    {content.intro && (
                        <p className="mt-10 text-lg leading-relaxed text-fg-muted">
                            <Txt value={content.intro} />
                        </p>
                    )}

                    {content.sections?.map((section) => (
                        <section key={section.title} className="mt-10">
                            <h2 className="text-2xl font-semibold text-ink">{section.title}</h2>
                            <p className="mt-3 leading-relaxed text-fg-muted">
                                <Txt value={section.text} />
                            </p>
                        </section>
                    ))}

                    {kind === 'cookie' && content.technical_cookies && (
                        <section className="mt-12">
                            <h2 className="text-2xl font-semibold text-ink">Cookie tecnici utilizzati</h2>
                            <p className="mt-3 leading-relaxed text-fg-muted">
                                Il sito utilizza esclusivamente i seguenti cookie tecnici, necessari al funzionamento. Non sono utilizzati cookie di profilazione né strumenti di analisi statistica.
                            </p>
                            <div className="mt-6 overflow-hidden rounded-card border border-line">
                                <table className="w-full text-left text-sm">
                                    <caption className="sr-only">Elenco dei cookie tecnici</caption>
                                    <thead className="bg-canvas">
                                        <tr>
                                            <th scope="col" className="px-4 py-3 font-semibold">Nome</th>
                                            <th scope="col" className="px-4 py-3 font-semibold">Finalità</th>
                                            <th scope="col" className="hidden px-4 py-3 font-semibold sm:table-cell">Durata</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-line">
                                        {content.technical_cookies.map((cookie) => (
                                            <tr key={cookie.name}>
                                                <td className="px-4 py-3 align-top font-mono text-xs break-all">{cookie.name}</td>
                                                <td className="px-4 py-3 align-top text-fg-muted">
                                                    {cookie.purpose}
                                                    <span className="mt-1 block text-xs sm:hidden">{cookie.duration}</span>
                                                </td>
                                                <td className="hidden px-4 py-3 align-top text-fg-muted sm:table-cell">{cookie.duration}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    )}
                </div>
            </Section>
        </PublicLayout>
    );
}
