export const DRAW_TIMES = ["09:30", "12:01", "14:00", "16:30"];
export function next3dDraw(parts) {
    const date =
        Number(parts.day) > 16
            ? new Date(Date.UTC(Number(parts.year), Number(parts.month), 1))
            : new Date(
                  Date.UTC(Number(parts.year), Number(parts.month) - 1, 16),
              );
    return new Intl.DateTimeFormat("en-US", {
        timeZone: "UTC",
        month: "short",
        day: "numeric",
    }).format(date);
}
export function permutations(value) {
    if (value.length <= 1) return [value];
    return [
        ...new Set(
            [...value].flatMap((digit, index) =>
                permutations(
                    value.slice(0, index) + value.slice(index + 1),
                ).map((rest) => digit + rest),
            ),
        ),
    ];
}
export function quickPick(type, group, value) {
    const size = Number(type) === 3 ? 1000 : 100;
    return Array.from({ length: size }, (_, n) =>
        String(n).padStart(Number(type), "0"),
    ).filter((number) => {
        const [a, b] = [...number].map(Number);
        switch (group) {
            case "even-even":
                return a % 2 === 0 && b % 2 === 0;
            case "even-odd":
                return a % 2 === 0 && b % 2 === 1;
            case "odd-even":
                return a % 2 === 1 && b % 2 === 0;
            case "odd-odd":
                return a % 2 === 1 && b % 2 === 1;
            case "double":
                return new Set(number).size === 1;
            case "contains":
                return number.includes(String(value));
            case "front":
                return number.startsWith(String(value));
            case "back":
                return number.endsWith(String(value));
            case "block":
                return (
                    Number(number) >= Number(value) &&
                    Number(number) <
                        Number(value) + (Number(type) === 3 ? 100 : 20)
                );
            case "snake":
                return (
                    [...number].every((digit) =>
                        String(value).includes(digit),
                    ) && number[0] !== number[1]
                );
            case "snake-double":
                return [...number].every((digit) =>
                    String(value).includes(digit),
                );
            case "star":
                return ["07", "18", "24", "35", "69"].includes(number);
            case "star-r":
                return ["07", "18", "24", "35", "69"]
                    .map((x) => x.split("").reverse().join(""))
                    .includes(number);
            case "power":
                return ["05", "16", "27", "38", "49"].includes(number);
            case "power-r":
                return ["05", "16", "27", "38", "49"]
                    .map((x) => x.split("").reverse().join(""))
                    .includes(number);
            default:
                return false;
        }
    });
}
export function toggleNumbers(selected, requested, numbers) {
    const allowed = new Set(
        numbers.filter((x) => !Number(x.disable)).map((x) => String(x.number)),
    );
    const result = new Set(selected);
    for (const value of new Set(requested))
        if (allowed.has(value)) {
            if (result.has(value)) result.delete(value);
            else result.add(value);
        }
    return result;
}
export function aggregateCart(rows) {
    const map = new Map();
    for (const row of rows) {
        const previous = map.get(Number(row.id));
        if (previous) previous.amount += Number(row.amount);
        else
            map.set(Number(row.id), {
                id: Number(row.id),
                number: String(row.number),
                amount: Number(row.amount),
                allowed_amount: Number(row.allowed_amount),
            });
    }
    return [...map.values()];
}
export function drawClosed(type, clock, parts, open = true) {
    if (!open || !clock) return true;
    const minutes = Number(parts.hour) * 60 + Number(parts.minute);
    const draw =
        ((Number(clock.hour) % 12) + (Number(clock.morning) ? 0 : 12)) * 60 +
        Number(clock.minute) -
        Number(clock.close_before || 0);
    if (Number(type) === 2)
        return ["Sat", "Sun"].includes(parts.weekday) || minutes >= draw;
    return [1, 16].includes(Number(parts.day)) && minutes >= draw;
}
export function weekdayCalendar(year, month, days) {
    const start = new Date(Date.UTC(year, month - 1, 1));
    let offset = (start.getUTCDay() + 6) % 7;
    // Weekend starts have no leading placeholder; the first weekday is Monday.
    if (offset >= 5) offset = 0;
    const map = new Map(days.map((day) => [day.date, day]));
    const result = Array(offset).fill(null);
    for (
        let date = 1;
        date <= new Date(Date.UTC(year, month, 0)).getUTCDate();
        date++
    ) {
        const d = new Date(Date.UTC(year, month - 1, date));
        if ([0, 6].includes(d.getUTCDay())) continue;
        const key = `${year}-${String(month).padStart(2, "0")}-${String(date).padStart(2, "0")}`;
        result.push(map.get(key) || { date: key, day: date, results: {} });
    }
    return result;
}
