import { startArea } from './lib/createApp';

startArea({ pages: import.meta.glob('./pages/Public/**/*.tsx'), xsrfCookie: 'XSRF-TOKEN' });
