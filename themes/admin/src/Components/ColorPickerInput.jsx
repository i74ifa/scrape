import {
    ColorPicker,
    ColorArea,
    ColorSlider,
    ColorField,
    ColorSwatch,
    parseColor,
} from "@heroui/react";

const DEFAULT = "#000000";

// react-aria's parseColor throws on empty/invalid input — fall back to black so
// the picker always has a valid starting color.
const safeParse = (v) => {
    try {
        return parseColor(v || DEFAULT);
    } catch {
        return parseColor(DEFAULT);
    }
};

/**
 * HeroUI color picker bound to a hex string. Trigger shows the current swatch;
 * the popover has a saturation/brightness area, a hue slider, and a hex field.
 * `onChange` receives a hex string (e.g. "#ff0000").
 */
export default function ColorPickerInput({ value, onChange, className = "" }) {
    return (
        <ColorPicker
            value={safeParse(value)}
            onChange={(color) => onChange(color.toString("hex"))}
        >
            <ColorPicker.Trigger
                title="اختر لوناً"
                className={`w-6 h-6 rounded-md overflow-hidden border border-zinc-300 dark:border-zinc-600 shrink-0 ${className}`}
            >
                <ColorSwatch color={value || DEFAULT} className="w-full h-full" />
            </ColorPicker.Trigger>
            <ColorPicker.Popover className="p-3 space-y-3 w-56">
                <ColorArea
                    colorSpace="hsb"
                    xChannel="saturation"
                    yChannel="brightness"
                >
                    <ColorArea.Thumb />
                </ColorArea>
                <ColorSlider colorSpace="hsb" channel="hue">
                    <ColorSlider.Track>
                        <ColorSlider.Thumb />
                    </ColorSlider.Track>
                </ColorSlider>
                <ColorField>
                    <ColorField.Group>
                        <ColorField.Input />
                    </ColorField.Group>
                </ColorField>
            </ColorPicker.Popover>
        </ColorPicker>
    );
}
