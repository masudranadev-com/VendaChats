const metaColorAPI = "http://localhost:8081/api/test/meta";
const themeCacheKey = "theme1-css-vars-v2";

const defaultThemeTokens = {
    primary_color: "#F28B00",
    secondary_color: "#F92400",
    body_bg: "#FFFFFF",
    surface_bg: "#FFFFFF",
    text_color: "#919191",
};

const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

const normalizeHex = (value) => {
    if (typeof value !== "string") {
        return null;
    }

    let hex = value.trim().replace("#", "");
    if (hex.length === 3) {
        hex = hex.split("").map((char) => char + char).join("");
    }

    if (!/^[0-9a-fA-F]{6}$/.test(hex)) {
        return null;
    }

    return `#${hex.toUpperCase()}`;
};

const hexToRgb = (hex) => {
    const normalized = normalizeHex(hex);
    if (!normalized) {
        return null;
    }

    const value = normalized.slice(1);
    return {
        r: parseInt(value.slice(0, 2), 16),
        g: parseInt(value.slice(2, 4), 16),
        b: parseInt(value.slice(4, 6), 16),
    };
};

const rgbToHex = ({ r, g, b }) => {
    const toHex = (channel) => clamp(Math.round(channel), 0, 255).toString(16).padStart(2, "0");
    return `#${toHex(r)}${toHex(g)}${toHex(b)}`.toUpperCase();
};

const mixHex = (baseColor, mixColor, amount) => {
    const base = hexToRgb(baseColor);
    const mix = hexToRgb(mixColor);
    const ratio = clamp(amount, 0, 1);

    if (!base || !mix) {
        return normalizeHex(baseColor) || normalizeHex(mixColor) || "#000000";
    }

    return rgbToHex({
        r: base.r + (mix.r - base.r) * ratio,
        g: base.g + (mix.g - base.g) * ratio,
        b: base.b + (mix.b - base.b) * ratio,
    });
};

const darkenHex = (hex, amount) => mixHex(hex, "#000000", amount);

const rgba = (hex, alpha) => {
    const rgb = hexToRgb(hex);
    if (!rgb) {
        return `rgba(0, 0, 0, ${alpha})`;
    }

    return `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${clamp(alpha, 0, 1)})`;
};

const relativeLuminance = (hex) => {
    const rgb = hexToRgb(hex);
    if (!rgb) {
        return 0;
    }

    const convert = (value) => {
        const normalized = value / 255;
        return normalized <= 0.03928
            ? normalized / 12.92
            : Math.pow((normalized + 0.055) / 1.055, 2.4);
    };

    return 0.2126 * convert(rgb.r) + 0.7152 * convert(rgb.g) + 0.0722 * convert(rgb.b);
};

const contrastRatio = (foreground, background) => {
    const l1 = relativeLuminance(foreground);
    const l2 = relativeLuminance(background);
    const lighter = Math.max(l1, l2);
    const darker = Math.min(l1, l2);

    return (lighter + 0.05) / (darker + 0.05);
};

const pickTextColor = (background, dark = "#0F172A", light = "#FFFFFF") => {
    return contrastRatio(dark, background) >= contrastRatio(light, background) ? dark : light;
};

const pickBrandTextColor = (
    background,
    preferred = "#FFFFFF",
    fallback = "#0F172A",
    minimumPreferredContrast = 3.25
) => {
    const preferredContrast = contrastRatio(preferred, background);
    const fallbackContrast = contrastRatio(fallback, background);

    if (preferredContrast >= minimumPreferredContrast) {
        return preferred;
    }

    return fallbackContrast >= preferredContrast ? fallback : preferred;
};

const getThemeEntries = (payload) => {
    if (Array.isArray(payload)) {
        return payload;
    }

    if (payload && Array.isArray(payload.data)) {
        return payload.data;
    }

    return [];
};

const themeEntriesToMap = (entries) => {
    return entries.reduce((map, entry) => {
        if (entry && entry.key) {
            map[entry.key] = entry.value;
        }
        return map;
    }, {});
};

const buildThemeVariables = (tokenMap) => {
    const primary = normalizeHex(tokenMap.primary_color) || defaultThemeTokens.primary_color;
    const secondary = normalizeHex(tokenMap.secondary_color) || defaultThemeTokens.secondary_color;
    const bodyBg = normalizeHex(tokenMap.body_bg) || defaultThemeTokens.body_bg;
    const surfaceBg = normalizeHex(tokenMap.surface_bg || tokenMap.card_bg) || defaultThemeTokens.surface_bg;
    const textColor = normalizeHex(tokenMap.text_color) || defaultThemeTokens.text_color;

    const heading = darkenHex(textColor, 0.45);
    const muted = mixHex(textColor, bodyBg, 0.45);
    const border = mixHex(textColor, surfaceBg, 0.82);
    const lightSurface = mixHex(primary, bodyBg, 0.92);
    const primaryHover = darkenHex(primary, 0.16);
    const secondaryHover = darkenHex(secondary, 0.16);
    const onPrimary = normalizeHex(tokenMap.primary_text) || pickBrandTextColor(primary);
    const onPrimaryHover = normalizeHex(tokenMap.primary_hover_text) || pickBrandTextColor(primaryHover);
    const onSecondary = normalizeHex(tokenMap.secondary_text) || pickBrandTextColor(secondary);
    const onSecondaryHover = normalizeHex(tokenMap.secondary_hover_text) || pickBrandTextColor(secondaryHover);
    const footerBase = mixHex(textColor, primary, 0.18);
    const footerBg = darkenHex(footerBase, 0.55);
    const footerBaseText = pickTextColor(footerBg);
    const footerText = mixHex(footerBaseText, footerBg, footerBaseText === "#FFFFFF" ? 0.18 : 0.10);

    return {
        "--theme-primary": primary,
        "--theme-primary-hover": primaryHover,
        "--theme-primary-soft": rgba(primary, 0.14),
        "--theme-secondary": secondary,
        "--theme-secondary-hover": secondaryHover,
        "--theme-secondary-soft": rgba(secondary, 0.14),
        "--theme-body-bg": bodyBg,
        "--theme-surface-bg": surfaceBg,
        "--theme-text": textColor,
        "--theme-heading": heading,
        "--theme-muted": muted,
        "--theme-border": border,
        "--theme-light": lightSurface,
        "--theme-on-primary": onPrimary,
        "--theme-on-primary-hover": onPrimaryHover,
        "--theme-on-secondary": onSecondary,
        "--theme-on-secondary-hover": onSecondaryHover,
        "--theme-footer-bg": footerBg,
        "--theme-footer-text": footerText,
        "--theme-footer-glass": rgba(footerBaseText, 0.06),
        "--theme-shadow-color": rgba(primary, 0.18),
        "--theme-focus-ring": rgba(primary, 0.24),
        "--theme-overlay-dark": rgba(darkenHex(primary, 0.70), 0.56),
        "--theme-overlay-accent": rgba(primary, 0.30),
        "--theme-overlay-accent-strong": rgba(primary, 0.50),
        "--theme-overlay-surface": rgba(surfaceBg, 0.20),
        "--theme-overlay-surface-strong": rgba(surfaceBg, 0.50),
    };
};

const applyThemeVariables = (variables) => {
    const root = document.documentElement;
    Object.keys(variables).forEach((key) => {
        root.style.setProperty(key, variables[key]);
    });
};

const restoreCachedTheme = () => {
    try {
        const raw = localStorage.getItem(themeCacheKey);
        if (!raw) {
            return;
        }

        applyThemeVariables(JSON.parse(raw));
    } catch (error) {
        console.warn("Failed to restore cached theme variables.", error);
    }
};

const cacheThemeVariables = (variables) => {
    try {
        localStorage.setItem(themeCacheKey, JSON.stringify(variables));
    } catch (error) {
        console.warn("Failed to cache theme variables.", error);
    }
};

const metaColorFunction = () => {
    $.ajax({
        url: metaColorAPI,
        method: "GET",
        success: function (payload) {
            const entries = getThemeEntries(payload);
            if (!entries.length) {
                console.warn("Theme API returned no theme entries.", payload);
                return;
            }

            const themeMap = themeEntriesToMap(entries);
            const variables = buildThemeVariables(themeMap);

            applyThemeVariables(variables);
            cacheThemeVariables(variables);
        },
        error: function (xhr, status, error) {
            console.error("Failed to load theme settings.", status, error);
        }
    });
};

restoreCachedTheme();
metaColorFunction();
