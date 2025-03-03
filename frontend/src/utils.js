export function formatDate(dateStr) {
    const date = new Date(dateStr);
    const now = new Date();

    // Массив для месяцев на русском
    const months = [
        'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
        'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'
    ];

    if (date < now) {
        // Разница в миллисекундах
        const diffTime = now - date;
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

        // Если дата вчера
        if (diffDays === 1) return `вчера`;

        // Если дата на прошлой неделе
        if (diffDays > 1 && diffDays <= 7) return `на прошлой неделе`;

        // Если дата в прошлом месяце
        if (now.getMonth() !== date.getMonth() && now.getFullYear() === date.getFullYear()) {
            return `в прошлом месяце`;
        }

        // Если дата в прошлом году
        if (now.getFullYear() !== date.getFullYear()) {
            const yearDiff = now.getFullYear() - date.getFullYear();
            return yearDiff === 1 ? `в прошлом году` : `${yearDiff} ${getYearWord(yearDiff)} назад`;
        }
    }

    // Форматирование стандартной даты
    const day = date.getDate();
    const month = months[date.getMonth()];
    const hours = date.getHours();
    const minutes = date.getMinutes();
    const formattedTime = `${hours < 10 ? '0' + hours : hours}:${minutes < 10 ? '0' + minutes : minutes}`;

    return `${day} ${month} · ${formattedTime} мск`;
}
export function getYearWord(years) {
    if (years % 10 === 1 && years % 100 !== 11) return 'год';
    if ([2, 3, 4].includes(years % 10) && ![12, 13, 14].includes(years % 100)) return 'года';
    return 'лет';
}

export function getRelativeDate(inputDateStr) {
    // Парсим входную дату
    const inputDate = new Date(inputDateStr);
    const now = new Date(); // Текущая дата, в твоём случае 2025-02-22

    // Устанавливаем начало дня для корректного сравнения
    const today = new Date(now.setHours(0, 0, 0, 0));
    const tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);
    const dayAfterTomorrow = new Date(today);
    dayAfterTomorrow.setDate(today.getDate() + 2);

    // Начало и конец текущей недели (неделя начинается с понедельника)
    const startOfWeek = new Date(today);
    startOfWeek.setDate(today.getDate() - today.getDay() + (today.getDay() === 0 ? -6 : 1));
    const endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(startOfWeek.getDate() + 6);
    const nextWeekEnd = new Date(endOfWeek);
    nextWeekEnd.setDate(endOfWeek.getDate() + 7);

    // Начало и конец текущего месяца
    const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    const endOfNextMonth = new Date(today.getFullYear(), today.getMonth() + 2, 0);

    // Начало и конец текущего года
    const startOfYear = new Date(today.getFullYear(), 0, 1);
    const endOfYear = new Date(today.getFullYear(), 11, 31);
    const endOfNextYear = new Date(today.getFullYear() + 1, 11, 31);

    // Сравниваем даты
    if (inputDate.toDateString() === tomorrow.toDateString()) {
        return "Завтра";
    } else if (inputDate.toDateString() === dayAfterTomorrow.toDateString()) {
        return "Послезавтра";
    } else if (inputDate >= startOfWeek && inputDate <= endOfWeek) {
        return "На этой неделе";
    } else if (inputDate > endOfWeek && inputDate <= nextWeekEnd) {
        return "На следующей неделе";
    } else if (inputDate >= startOfMonth && inputDate <= endOfMonth) {
        return "В этом месяце";
    } else if (inputDate > endOfMonth && inputDate <= endOfNextMonth) {
        return "В следующем месяце";
    } else if (inputDate >= startOfYear && inputDate <= endOfYear) {
        return "В этом году";
    } else if (inputDate > endOfYear && inputDate <= endOfNextYear) {
        return "В следующем году";
    } else {
        return ""; // Ничего, если дата дальше следующего года
    }
}

export function generateSoftRandomColor() {
    // Генерируем случайные значения RGB с ограничением, чтобы избежать слишком ярких цветов
    const r = Math.floor(Math.random() * 128) + 64; // 64–191 (средние значения для мягкости)
    const g = Math.floor(Math.random() * 128) + 64; // 64–191
    const b = Math.floor(Math.random() * 128) + 64; // 64–191
    return `rgb(${r}, ${g}, ${b})`;
}
export function getTextColor(backgroundColor) {
    // Проверяем яркость цвета и возвращаем чёрный или белый текст
    if (backgroundColor.startsWith('rgb')) {
        const rgb = backgroundColor.match(/\d+/g).map(Number);
        const r = rgb[0];
        const g = rgb[1];
        const b = rgb[2];
        const brightness = ((r * 299) + (g * 587) + (b * 114)) / 1000;
        return brightness > 128 ? '#000000' : '#FFFFFF';
    }
    return '#000000'; // Значение по умолчанию
}