import test from "node:test";
import assert from "node:assert/strict";
import {
    permutations,
    quickPick,
    toggleNumbers,
    aggregateCart,
    drawClosed,
    weekdayCalendar,
    next3dDraw,
} from "../../public/client/logic.js";

test("reverse selections retain zeroes and deduplicate repeated digits", () => {
    assert.deepEqual(permutations("007").sort(), ["007", "070", "700"]);
    assert.equal(permutations("123").length, 6);
});
test("native quick-pick groups contain exact numbers", () => {
    assert.equal(quickPick(2, "even-even").length, 25);
    assert.deepEqual(quickPick(2, "star"), ["07", "18", "24", "35", "69"]);
    assert.deepEqual(quickPick(2, "star-r"), ["42", "53", "70", "81", "96"]);
    assert.deepEqual(quickPick(2, "snake", "012"), [
        "01",
        "02",
        "10",
        "12",
        "20",
        "21",
    ]);
    assert.equal(quickPick(2, "snake-double", "012").length, 9);
    assert.equal(quickPick(3, "double").length, 10);
    assert.equal(quickPick(3, "block", 900).length, 100);
});
test("disabled numbers are excluded and repeated matching selections toggle", () => {
    const numbers = [
        { number: "07", disable: 0 },
        { number: "70", disable: 1 },
    ];
    assert.deepEqual(
        [...toggleNumbers(new Set(), ["07", "70"], numbers)],
        ["07"],
    );
    assert.equal(toggleNumbers(new Set(["07"]), ["07"], numbers).size, 0);
});
test("paired hot numbers are aggregated before capacity validation and submission", () => {
    assert.deepEqual(
        aggregateCart([
            { id: 1, number: "07", amount: "100", allowed_amount: 500 },
            { id: 1, number: "07", amount: 200, allowed_amount: 500 },
        ]),
        [{ id: 1, number: "07", amount: 300, allowed_amount: 500 }],
    );
});
test("Myanmar closing boundaries and 3D non-draw days match the native rules", () => {
    const clock = { hour: 12, minute: 1, morning: 0, close_before: 30 };
    assert.equal(
        drawClosed(2, clock, { hour: 11, minute: 30, weekday: "Mon", day: 9 }),
        false,
    );
    assert.equal(
        drawClosed(2, clock, { hour: 11, minute: 31, weekday: "Mon", day: 9 }),
        true,
    );
    assert.equal(
        drawClosed(2, clock, { hour: 9, minute: 30, weekday: "Sat", day: 7 }),
        true,
    );
    assert.equal(
        drawClosed(3, clock, { hour: 18, minute: 0, weekday: "Mon", day: 9 }),
        false,
    );
    assert.equal(
        drawClosed(3, clock, { hour: 18, minute: 0, weekday: "Mon", day: 16 }),
        true,
    );
});
test("weekday calendar aligns months, includes every weekday, and leaves missing slots empty", () => {
    const november = weekdayCalendar(2026, 11, []);
    assert.equal(november.length, 21);
    assert.equal(november[0].date, "2026-11-02");
    const july = weekdayCalendar(2026, 7, [
        { date: "2026-07-01", day: 1, results: { "09:30": { number: "07" } } },
    ]);
    assert.equal(july[0], null);
    assert.equal(july[1], null);
    assert.equal(july[2].results["09:30"].number, "07");
    assert.equal(weekdayCalendar(2024, 2, []).filter(Boolean).length, 21);
});
test("3D review retains native next-draw dates including year rollover", () => {
    assert.equal(next3dDraw({ year: 2026, month: 11, day: 9 }), "Nov 16");
    assert.equal(next3dDraw({ year: 2026, month: 12, day: 17 }), "Jan 1");
});
