import { useEffect } from "react";
import { useTranslation } from "react-i18next";
import { changeLanguage, isRTL } from "@/lib/i18n";

export default function LanguageSwitcher({ locale }) {
    const { i18n } = useTranslation();

    useEffect(() => {
        if (locale && i18n.language !== locale) {
            changeLanguage(locale);
        }
    }, [locale]);

    useEffect(() => {
        document.documentElement.dir = isRTL() ? "rtl" : "ltr";
        document.documentElement.lang = i18n.language;
    }, [i18n.language]);

    return null;
}