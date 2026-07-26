import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

import frPilgrimage from './fr/pilgrimage.json';
import nlPilgrimage from './nl/pilgrimage.json';
import dePilgrimage from './de/pilgrimage.json';

void i18n
  .use(initReactI18next)
  .init({
    lng: navigator.language.startsWith('nl') ? 'nl' : navigator.language.startsWith('de') ? 'de' : 'fr',
    fallbackLng: 'fr',
    interpolation: {
      escapeValue: false,
    },
    resources: {
      fr: { pilgrimage: frPilgrimage.pilgrimage },
      nl: { pilgrimage: nlPilgrimage.pilgrimage },
      de: { pilgrimage: dePilgrimage.pilgrimage },
    },
  });

export default i18n;
