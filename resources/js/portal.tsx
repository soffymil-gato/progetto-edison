import { startArea } from './lib/createApp';

startArea({
    pages: {
        ...import.meta.glob('./pages/Portal/**/*.tsx'),
        ...import.meta.glob('./pages/Auth/**/*.tsx'),
        ...import.meta.glob('./pages/Account/**/*.tsx'),
    },
    xsrfCookie: 'XSRF-TOKEN-PORTAL',
});
