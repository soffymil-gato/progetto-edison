import js from '@eslint/js';
import reactHooks from 'eslint-plugin-react-hooks';
import globals from 'globals';
import tseslint from 'typescript-eslint';

export default tseslint.config(
    { ignores: ['public/build/**', 'vendor/**', 'node_modules/**', 'bootstrap/ssr/**'] },
    js.configs.recommended,
    ...tseslint.configs.strict,
    {
        files: ['resources/js/**/*.{ts,tsx}', 'tests/e2e/**/*.ts'],
        languageOptions: { globals: { ...globals.browser, ...globals.node } },
        plugins: { 'react-hooks': reactHooks },
        rules: {
            ...reactHooks.configs.recommended.rules,
            '@typescript-eslint/consistent-type-imports': 'error',
            'no-console': ['error', { allow: ['warn', 'error'] }],
        },
    },
);
