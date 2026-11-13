import { useForm } from '@inertiajs/react';
import { CheckCheck, Mail, Phone } from 'lucide-react';
import type { FormEvent } from 'react';

import { Badge } from '@/design-system/Badge';
import { Button } from '@/design-system/Button';
import { Card, CardBody, CardHeader } from '@/design-system/Card';
import { DescriptionList } from '@/design-system/DescriptionList';
import { Field } from '@/design-system/Field';
import { Textarea } from '@/design-system/Input';
import { PageHeader } from '@/design-system/PageHeader';
import { AppShell } from '@/layouts/AppShell';
import { formatDateTime } from '@/lib/format';

type Props = {
    request: {
        id: number;
        name: string;
        company: string | null;
        email: string;
        phone: string | null;
        subject: string;
        message: string;
        status: string;
        statusLabel: string;
        createdAt: string;
        privacyAcceptedAt: string;
        handledAt: string | null;
        handledBy: string | null;
        handlingNote: string | null;
    };
    can: { manage: boolean };
    urls: { back: string; handled: string };
};

export default function ContactRequestShow({ request, can, urls }: Props) {
    const form = useForm({ note: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.patch(urls.handled, { preserveScroll: true });
    };

    return (
        <AppShell title={request.subject}>
            <PageHeader
                back={{ href: urls.back, label: 'Richieste di contatto' }}
                eyebrow={`Richiesta #${request.id}`}
                title={request.subject}
                description={`Ricevuta il ${formatDateTime(request.createdAt)}`}
                actions={
                    <Badge tone={request.status === 'new' ? 'warning' : 'success'} dot>
                        {request.statusLabel}
                    </Badge>
                }
            />
            <div className="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
                <Card>
                    <CardHeader title="Messaggio" />
                    <CardBody>
                        <p className="text-[0.9375rem] leading-relaxed whitespace-pre-line text-fg">
                            {request.message}
                        </p>
                    </CardBody>
                </Card>
                <div className="flex flex-col gap-6">
                    <Card>
                        <CardHeader title="Mittente" />
                        <CardBody className="py-2">
                            <DescriptionList
                                items={[
                                    { term: 'Nome', value: request.name },
                                    { term: 'Azienda', value: request.company ?? '—' },
                                    {
                                        term: 'Email',
                                        value: (
                                            <a
                                                className="inline-flex items-center gap-1.5 font-medium text-brand-700 hover:underline"
                                                href={`mailto:${request.email}`}
                                            >
                                                <Mail className="size-3.5" aria-hidden />
                                                {request.email}
                                            </a>
                                        ),
                                    },
                                    {
                                        term: 'Telefono',
                                        value: request.phone ? (
                                            <a
                                                className="inline-flex items-center gap-1.5 font-medium text-brand-700 hover:underline"
                                                href={`tel:${request.phone.replace(/\s+/g, '')}`}
                                            >
                                                <Phone className="size-3.5" aria-hidden />
                                                {request.phone}
                                            </a>
                                        ) : (
                                            '—'
                                        ),
                                    },
                                    {
                                        term: 'Informativa privacy',
                                        value: `Presa visione il ${formatDateTime(request.privacyAcceptedAt)}`,
                                    },
                                ]}
                            />
                        </CardBody>
                    </Card>
                    <Card>
                        <CardHeader title="Gestione" />
                        <CardBody>
                            {request.status === 'handled' ? (
                                <div className="space-y-2 text-sm">
                                    <p>
                                        Gestita da <span className="font-medium">{request.handledBy ?? '—'}</span> il{' '}
                                        {formatDateTime(request.handledAt)}.
                                    </p>
                                    {request.handlingNote && (
                                        <p className="rounded-lg bg-canvas px-3 py-2.5 whitespace-pre-line text-fg-muted">
                                            {request.handlingNote}
                                        </p>
                                    )}
                                </div>
                            ) : can.manage ? (
                                <form onSubmit={submit} className="space-y-4" noValidate>
                                    <Field
                                        label="Nota (facoltativa)"
                                        error={form.errors.note}
                                        hint="Ad esempio come e quando è stato ricontattato il mittente."
                                    >
                                        {(f) => (
                                            <Textarea
                                                id={f.id}
                                                aria-describedby={f.describedBy}
                                                invalid={f.invalid}
                                                rows={3}
                                                maxLength={1000}
                                                value={form.data.note}
                                                onChange={(e) => form.setData('note', e.target.value)}
                                            />
                                        )}
                                    </Field>
                                    <Button
                                        type="submit"
                                        loading={form.processing}
                                        icon={<CheckCheck className="size-4" aria-hidden />}
                                    >
                                        Segna come gestita
                                    </Button>
                                </form>
                            ) : (
                                <p className="text-sm text-fg-muted">Non hai il permesso di gestire le richieste.</p>
                            )}
                        </CardBody>
                    </Card>
                </div>
            </div>
        </AppShell>
    );
}
