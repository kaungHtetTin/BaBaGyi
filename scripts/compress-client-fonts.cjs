const fs = require("fs");
const path = require("path");
const { compress } = require("wawoff2");
(async () => {
    const directory = path.resolve(__dirname, "../public/client/fonts");
    for (const name of fs
        .readdirSync(directory)
        .filter((x) => /\.(ttf|otf)$/i.test(x))) {
        const source = fs.readFileSync(path.join(directory, name));
        const output = Buffer.from(await compress(source));
        const destination = name.replace(/\.(ttf|otf)$/i, ".woff2");
        fs.writeFileSync(path.join(directory, destination), output);
        fs.unlinkSync(path.join(directory, name));
        console.log(
            `${destination}: ${source.length} → ${output.length} bytes`,
        );
    }
})().catch((error) => {
    console.error(error);
    process.exitCode = 1;
});
