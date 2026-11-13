import type { InertiaFormProps } from '@inertiajs/react';

import { Field } from '@/design-system/Field';
import { PasswordInput } from '@/design-system/Input';

type PasswordForm = { password: string; password_confirmation: string };

/** New password + confirmation, with the length policy explained to the user. */
export function PasswordFields<T extends PasswordForm>({ form, minLength, label = 'Nuova password' }: { form: InertiaFormProps<T>; minLength: number; label?: string }) {
    const data = form.data as PasswordForm;
    const errors = form.errors as Partial<Record<keyof PasswordForm, string>>;
    const set = form.setData as unknown as (key: keyof PasswordForm, value: string) => void;
    const long = data.password.length >= minLength;

    return (
        <>
            <Field label={label} error={errors.password} hint={`Almeno ${minLength} caratteri. Puoi usare una frase facile da ricordare.`} required>
                {(field) => (
                    <PasswordInput
                        id={field.id}
                        aria-describedby={field.describedBy}
                        invalid={field.invalid}
                        value={data.password}
                        onChange={(e) => set('password', e.target.value)}
                        autoComplete="new-password"
                        minLength={minLength}
                        required
                    />
                )}
            </Field>
            <div className="-mt-2 flex items-center gap-2 text-xs" aria-live="polite">
                <span className={long ? 'h-1.5 flex-1 rounded-full bg-success-600' : 'h-1.5 flex-1 rounded-full bg-line'} aria-hidden />
                <span className={long ? 'text-success-700' : 'text-fg-subtle'}>
                    {data.password.length}/{minLength} caratteri
                </span>
            </div>
            <Field label="Conferma la password" error={errors.password_confirmation} required>
                {(field) => (
                    <PasswordInput
                        id={field.id}
                        aria-describedby={field.describedBy}
                        invalid={field.invalid}
                        value={data.password_confirmation}
                        onChange={(e) => set('password_confirmation', e.target.value)}
                        autoComplete="new-password"
                        required
                    />
                )}
            </Field>
        </>
    );
}
