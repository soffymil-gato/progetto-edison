import { useForm } from '@inertiajs/react';
import { Save } from 'lucide-react';
import type { FormEvent } from 'react';

import { Badge } from '@/design-system/Badge';
import { Button } from '@/design-system/Button';
import { Card, CardBody, CardHeader } from '@/design-system/Card';
import { Field } from '@/design-system/Field';
import { Input, Textarea } from '@/design-system/Input';
import { PageHeader } from '@/design-system/PageHeader';
import { AppShell } from '@/layouts/AppShell';

type FieldDef = {
    name: string;
    label: string;
    type: 'text' | 'textarea' | 'email' | 'tel';
    max: number;
    release: boolean;
    help?: string;
};
type Group = {
    key: string;
    label: string;
    description: string;
    fields: FieldDef[];
    values: Record<string, string | null>;
    submitUrl: string;
};

export default function SettingsEdit({ groups }: { groups: Group[] }) {
    return (
        <AppShell title="Impostazioni">
            <PageHeader
                title="Impostazioni"
                description="Dati aziendali reali, forniti da Edison. Ogni modifica è registrata nel registro attività. I campi «Richiesto per il rilascio» devono essere compilati prima della pubblicazione."
            />
            <div className="flex flex-col gap-6">
                {groups.map((group) => (
                    <SettingsGroup key={group.key} group={group} />
                ))}
            </div>
        </AppShell>
    );
}

function SettingsGroup({ group }: { group: Group }) {
    const initial = Object.fromEntries(group.fields.map((f) => [f.name, group.values[f.name] ?? '']));
    const form = useForm<Record<string, string>>(initial);
    const missing = group.fields.filter((f) => f.release && !group.values[f.name]).length;

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put(group.submitUrl, { preserveScroll: true });
    };

    return (
        <Card id={group.key}>
            <CardHeader
                title={group.label}
                description={group.description}
                actions={
                    missing > 0 ? (
                        <Badge tone="warning">{missing} da completare</Badge>
                    ) : (
                        <Badge tone="success">Completo</Badge>
                    )
                }
            />
            <CardBody>
                <form onSubmit={submit} className="grid gap-5 md:grid-cols-2" noValidate>
                    {group.fields.map((def) => (
                        <Field
                            key={def.name}
                            label={
                                <span className="inline-flex flex-wrap items-center gap-2">
                                    {def.label}
                                    {def.release && (
                                        <span className="text-xs font-normal text-warning-700">
                                            Richiesto per il rilascio
                                        </span>
                                    )}
                                </span>
                            }
                            hint={def.help}
                            error={form.errors[def.name]}
                            className={def.type === 'textarea' ? 'md:col-span-2' : undefined}
                        >
                            {(f) =>
                                def.type === 'textarea' ? (
                                    <Textarea
                                        id={f.id}
                                        aria-describedby={f.describedBy}
                                        invalid={f.invalid}
                                        rows={3}
                                        maxLength={def.max}
                                        value={form.data[def.name] ?? ''}
                                        onChange={(e) => form.setData(def.name, e.target.value)}
                                    />
                                ) : (
                                    <Input
                                        id={f.id}
                                        type={def.type}
                                        aria-describedby={f.describedBy}
                                        invalid={f.invalid}
                                        maxLength={def.max}
                                        value={form.data[def.name] ?? ''}
                                        onChange={(e) => form.setData(def.name, e.target.value)}
                                        autoComplete="off"
                                    />
                                )
                            }
                        </Field>
                    ))}
                    <div className="flex items-center justify-end gap-3 border-t border-line pt-5 md:col-span-2">
                        {form.isDirty && <span className="text-sm text-fg-muted">Modifiche non salvate</span>}
                        <Button
                            type="submit"
                            loading={form.processing}
                            disabled={!form.isDirty}
                            icon={<Save className="size-4" aria-hidden />}
                        >
                            Salva
                        </Button>
                    </div>
                </form>
            </CardBody>
        </Card>
    );
}
