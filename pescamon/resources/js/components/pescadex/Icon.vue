<script setup lang="ts">
import { computed } from 'vue';
import type { FunctionalComponent } from 'vue';
import {
    Anchor,
    Apple,
    ArrowLeft,
    ArrowUp,
    Award,
    BatteryFull,
    Bell,
    Camera,
    Check,
    ChevronDown,
    ChevronRight,
    Circle,
    Crown,
    EyeOff,
    Fish,
    Flame,
    Gem,
    Heart,
    Image,
    Inbox,
    LayoutGrid,
    LocateFixed,
    Lock,
    Mail,
    Map,
    MapPin,
    MessageCircle,
    Moon,
    MoreHorizontal,
    Newspaper,
    Plus,
    Search,
    Settings,
    Share2,
    Shield,
    Signal,
    SlidersHorizontal,
    Sparkles,
    Sun,
    Sunrise,
    Target,
    Trophy,
    User,
    UserPlus,
    Users,
    Waves,
    Wifi,
    X,
    Zap,
} from '@lucide/vue';

// Explicit registry keyed by the kebab-case ids used across the screens, so
// Vite only bundles the icons we reference. Add new icons here as needed.
const registry: Record<string, FunctionalComponent> = {
    anchor: Anchor,
    apple: Apple,
    'arrow-left': ArrowLeft,
    'arrow-up': ArrowUp,
    award: Award,
    'battery-full': BatteryFull,
    bell: Bell,
    camera: Camera,
    check: Check,
    'chevron-down': ChevronDown,
    'chevron-right': ChevronRight,
    crown: Crown,
    'eye-off': EyeOff,
    fish: Fish,
    flame: Flame,
    gem: Gem,
    heart: Heart,
    image: Image,
    inbox: Inbox,
    'layout-grid': LayoutGrid,
    'locate-fixed': LocateFixed,
    lock: Lock,
    mail: Mail,
    map: Map,
    'map-pin': MapPin,
    'message-circle': MessageCircle,
    moon: Moon,
    'more-horizontal': MoreHorizontal,
    newspaper: Newspaper,
    plus: Plus,
    search: Search,
    settings: Settings,
    'share-2': Share2,
    shield: Shield,
    signal: Signal,
    'sliders-horizontal': SlidersHorizontal,
    sparkles: Sparkles,
    sun: Sun,
    sunrise: Sunrise,
    target: Target,
    trophy: Trophy,
    user: User,
    'user-plus': UserPlus,
    users: Users,
    waves: Waves,
    wifi: Wifi,
    x: X,
    zap: Zap,
};

const props = withDefaults(
    defineProps<{
        name: string;
        size?: number | string;
        color?: string;
        strokeWidth?: number;
        fill?: string;
    }>(),
    { size: 20, color: 'currentColor', strokeWidth: 2, fill: 'none' },
);

const component = computed<FunctionalComponent>(() => registry[props.name] ?? Circle);

// Drive colour through `style` so CSS custom properties like var(--teal)
// resolve — lucide sets stroke to currentColor, which inherits inline color.
const style = computed(() => {
    const s: Record<string, string> = { color: props.color };
    if (props.fill && props.fill !== 'none') s.fill = props.fill;
    return s;
});
</script>

<template>
    <component
        :is="component"
        :size="Number(size)"
        :stroke-width="strokeWidth"
        :style="style"
    />
</template>
