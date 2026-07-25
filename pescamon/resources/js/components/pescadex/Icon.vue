<script setup lang="ts">
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
    Eye,
    EyeOff,
    Fish,
    Flame,
    Gem,
    Heart,
    Image,
    Inbox,
    KeyRound,
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
    Package,
    Pencil,
    Plus,
    Search,
    Settings,
    ShoppingBag,
    Store,
    Share2,
    Shield,
    Signal,
    SlidersHorizontal,
    Sparkles,
    Sun,
    Sunrise,
    Target,
    Trash2,
    Trophy,
    Upload,
    User,
    UserPlus,
    Users,
    Waves,
    Wifi,
    X,
    Zap,
} from '@lucide/vue';
import { computed } from 'vue';
import type { FunctionalComponent } from 'vue';

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
    eye: Eye,
    'eye-off': EyeOff,
    fish: Fish,
    flame: Flame,
    gem: Gem,
    heart: Heart,
    image: Image,
    inbox: Inbox,
    'key-round': KeyRound,
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
    package: Package,
    pencil: Pencil,
    plus: Plus,
    search: Search,
    settings: Settings,
    'shopping-bag': ShoppingBag,
    store: Store,
    'share-2': Share2,
    shield: Shield,
    signal: Signal,
    'sliders-horizontal': SlidersHorizontal,
    sparkles: Sparkles,
    sun: Sun,
    sunrise: Sunrise,
    target: Target,
    'trash-2': Trash2,
    trophy: Trophy,
    upload: Upload,
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

const component = computed<FunctionalComponent>(
    () => registry[props.name] ?? Circle,
);

// Drive colour through `style` so CSS custom properties like var(--teal)
// resolve — lucide sets stroke to currentColor, which inherits inline color.
const style = computed(() => {
    const s: Record<string, string> = { color: props.color };

    if (props.fill && props.fill !== 'none') {
        s.fill = props.fill;
    }

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
