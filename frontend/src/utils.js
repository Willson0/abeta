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

export function getRelativeDate(eventDateStr) {
    // const now = new Date();
    // const moscowOffset = 3 * 60; // Смещение Москвы в минутах (UTC+3)
    // const localOffset = now.getTimezoneOffset(); // Локальное смещение в минутах
    // now.setMinutes(now.getMinutes() + localOffset + moscowOffset);
    //
    // // Дата события
    // const eventDate = new Date(eventDateStr);
    // eventDate.setMinutes(eventDate.getMinutes() + localOffset + moscowOffset);
    //
    // // Разница в миллисекундах
    // const diffMs = eventDate - now;
    // if (diffMs < 0) return "";
    //
    // const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
    // const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    //
    // // Определяем начало и конец текущей недели (неделя начинается с понедельника)
    // const startOfWeek = new Date(now);
    // startOfWeek.setDate(now.getDate() - (now.getDay() || 7) + 1);
    // startOfWeek.setHours(0, 0, 0, 0);
    //
    // const endOfWeek = new Date(startOfWeek);
    // endOfWeek.setDate(startOfWeek.getDate() + 6);
    // endOfWeek.setHours(23, 59, 59, 999);
    //
    // const startOfNextWeek = new Date(endOfWeek);
    // startOfNextWeek.setDate(endOfWeek.getDate() + 1);
    // startOfNextWeek.setHours(0, 0, 0, 0);
    //
    // const endOfNextWeek = new Date(startOfNextWeek);
    // endOfNextWeek.setDate(startOfNextWeek.getDate() + 6);
    // endOfNextWeek.setHours(23, 59, 59, 999);
    //
    // // Определяем границы месяца и года
    // const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
    // const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    // endOfMonth.setHours(23, 59, 59, 999);
    //
    // const startOfNextMonth = new Date(endOfMonth);
    // startOfNextMonth.setDate(endOfMonth.getDate() + 1);
    // const endOfNextMonth = new Date(now.getFullYear(), now.getMonth() + 2, 0);
    //
    // const endOfYear = new Date(now.getFullYear(), 11, 31);
    // endOfYear.setHours(23, 59, 59, 999);
    //
    // // Логика определения периода
    // if (diffHours < 24 && diffDays === 0) {
    //     return "Сегодня";
    // } else if (diffDays === 1) {
    //     return "Завтра";
    // } else if (diffDays === 2) {
    //     return "Послезавтра";
    // } else if (eventDate >= startOfWeek && eventDate <= endOfWeek) {
    //     return "На этой неделе";
    // } else if (eventDate >= startOfNextWeek && eventDate <= endOfNextWeek) {
    //     return "На следующей неделе";
    // } else if (eventDate >= startOfMonth && eventDate <= endOfMonth) {
    //     return "В этом месяце";
    // } else if (eventDate >= startOfNextMonth && eventDate <= endOfNextMonth) {
    //     return "В следующем месяце";
    // } else if (eventDate.getFullYear() === now.getFullYear()) {
    //     return "В этом году";
    // } else if (eventDate.getFullYear() === now.getFullYear() + 1) {
    //     return "В следующем году";
    // } else {
    //     return "Более чем через год";
    // }

    const date = new Date(eventDateStr);
    const months = [
        "янв", "фев", "мар", "апр", "май", "июн",
        "июл", "авг", "сен", "окт", "ноя", "дек"
    ];

    let day = date.getDate();
    let month = date.getMonth();
    let year = date.getFullYear();

    return `${day.toString().padStart(2, "0")}.${month.toString().padStart(2, "0")}.${year.toString().padStart(2, "0")}`;

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