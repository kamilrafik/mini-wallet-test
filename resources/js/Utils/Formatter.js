export class Formatter {
    static asCurrency(value) {
        const number = Number(value)
        if (isNaN(number)) {
            console.warn('NaN:', value);
            return '(unknown)'
        }
        const locale = navigator.language
        return new Intl.NumberFormat(locale, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(number)
    }

    static asDateTime(value) {
        const date = new Date(value)
        const locale = navigator.language
        return new Intl.DateTimeFormat(locale, {
            dateStyle: 'medium',
            timeStyle: 'short',
        }).format(date)
    }
}
