import { Link, useForm } from '@inertiajs/react';
import { CircleCheck, Send } from 'lucide-react';
import type { FormEvent } from 'react';

import { Button } from '@/design-system/Button';
import { Field } from '@/design-system/Field';
import { Checkbox, Input, Textarea } from '@/design-system/Input';
import { useShared } from '@/lib/useShared';

/**
 * Public contact form. Validation, anti-spam (honeypot + rate limit) and
 * storage happen on the server; the request is visible in the admin panel.
 */
export function ContactForm({ storeUrl }: { storeUrl: string }) {
    const { urls } = useShared();
    const form = useForm({ name: '', company: '', email: '', phone: '', subject: '', message: '', privacy: false, website: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(storeUrl, { preserveScroll: true, onSuccess: () => form.reset() });
    };

    if (form.recentlySuccessful) {
        return (
            <div className="flex flex-col items-center rounded-panel border border-success-600/20 bg-success-50 px-6 py-14 text-center" role="status">
                <CircleCheck className="size-12 text-success-600" aria-hidden />
                <p className="mt-4 font-display text-xl font-semibold text-fg">Richiesta inviata</p>
                <p className="mt-2 max-w-sm text-fg-muted">Grazie per averci scritto: ti risponderemo il prima possibile.</p>
            </div>
        );
    }

    return (
        <form onSubmit={submit} className="grid gap-5 sm:grid-cols-2" noValidate>
            <Field label="Nome e cognome" error={form.errors.name} required>
                {(f) => <Input id={f.id} aria-describedby={f.describedBy} invalid={f.invalid} value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} autoComplete="name" required />}
            </Field>
            <Field label="Azienda" error={form.errors.company}>
                {(f) => <Input id={f.id} aria-describedby={f.describedBy} invalid={f.invalid} value={form.data.company} onChange={(e) => form.setData('company', e.target.value)} autoComplete="organization" />}
            </Field>
            <Field label="Email" error={form.errors.email} required>
                {(f) => <Input id={f.id} type="email" aria-describedby={f.describedBy} invalid={f.invalid} value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} autoComplete="email" required />}
            </Field>
            <Field label="Telefono" error={form.errors.phone}>
                {(f) => <Input id={f.id} type="tel" aria-describedby={f.describedBy} invalid={f.invalid} value={form.data.phone} onChange={(e) => form.setData('phone', e.target.value)} autoComplete="tel" />}
            </Field>
            <Field label="Oggetto" error={form.errors.subject} required className="sm:col-span-2">
                {(f) => <Input id={f.id} aria-describedby={f.describedBy} invalid={f.invalid} value={form.data.subject} onChange={(e) => form.setData('subject', e.target.value)} maxLength={160} required />}
            </Field>
            <Field label="Messaggio" error={form.errors.message} required className="sm:col-span-2">
                {(f) => <Textarea id={f.id} aria-describedby={f.describedBy} invalid={f.invalid} rows={6} value={form.data.message} onChange={(e) => form.setData('message', e.target.value)} maxLength={5000} required />}
            </Field>

            {/* Honeypot: hidden from people and assistive technologies. */}
            <div className="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden>
                <label>
                    Sito web
                    <input type="text" tabIndex={-1} autoComplete="off" value={form.data.website} onChange={(e) => form.setData('website', e.target.value)} />
                </label>
            </div>

            <div className="sm:col-span-2">
                <label className="flex cursor-pointer items-start gap-3 text-sm leading-relaxed text-fg-muted">
                    <Checkbox className="mt-0.5" checked={form.data.privacy} onChange={(e) => form.setData('privacy', e.target.checked)} aria-invalid={Boolean(form.errors.privacy) || undefined} />
                    <span>
                        Dichiaro di aver preso visione dell'
                        <Link href={urls.privacy ?? '#'} className="font-medium text-brand-700 underline underline-offset-2">
                            informativa privacy
                        </Link>
                        . <span className="text-danger-600" aria-hidden>*</span>
                    </span>
                </label>
                {form.errors.privacy && <p className="mt-1.5 text-sm text-danger-700" role="alert">{form.errors.privacy}</p>}
            </div>

            <div className="flex flex-col gap-3 sm:col-span-2 sm:flex-row sm:items-center sm:justify-between">
                <p className="text-xs text-fg-subtle">I campi contrassegnati con * sono obbligatori.</p>
                <Button type="submit" size="lg" loading={form.processing} icon={<Send className="size-4" aria-hidden />}>
                    Invia la richiesta
                </Button>
            </div>
        </form>
    );
}
