import EditorModule from "react-simple-code-editor";
import Prism from "prismjs";

// `react-simple-code-editor` is CJS (`exports.default = Editor`); depending on
// the bundler's interop the default import may arrive as `{ default: Editor }`.
// Unwrap so `<Editor>` is always the component, not an object (React err #130).
const Editor = EditorModule?.default ?? EditorModule;
import "prismjs/components/prism-clike";
import "prismjs/components/prism-dart";
import "prismjs/themes/prism-tomorrow.css";

/**
 * Lightweight in-page code editor for Dart / Remote Flutter Widget code.
 * Wraps `react-simple-code-editor` with Prism syntax highlighting. The
 * surface is always dark (like a real editor) regardless of the admin
 * panel theme, and Tab inserts spaces instead of moving focus.
 */
export default function DartCodeEditor({
    value,
    onChange,
    placeholder = "// اكتب كود الـ Dart هنا",
    minRows = 12,
}) {
    const highlight = (code) =>
        Prism.highlight(code || "", Prism.languages.dart, "dart");

    return (
        <div className="rounded-2xl overflow-hidden border border-zinc-800 bg-[#1d1f21]">
            <Editor
                value={value ?? ""}
                onValueChange={onChange}
                highlight={highlight}
                placeholder={placeholder}
                padding={16}
                tabSize={2}
                insertSpaces
                textareaClassName="focus:outline-none"
                style={{
                    fontFamily:
                        'ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace',
                    fontSize: 13,
                    lineHeight: 1.6,
                    minHeight: `${minRows * 22}px`,
                    color: "#e5e7eb",
                    direction: "ltr",
                    textAlign: "left",
                }}
            />
        </div>
    );
}
