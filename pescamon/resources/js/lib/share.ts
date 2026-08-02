/**
 * Sharing helpers for catches. Getting a photo into an Instagram Story from
 * the web is only possible through the native Web Share API with a file
 * (`navigator.share({ files })`) on a mobile browser — there is no reliable
 * direct-to-Story deep link. WhatsApp additionally supports a universal
 * `wa.me` text/link fallback that works on every platform.
 */
import { storageUrl } from '@/lib/pescadex';

export interface ShareableCatch {
    id: number;
    species: { name: string };
    weight_kg: number;
    image_path: string | null;
}

export type ShareResult = 'shared' | 'cancelled' | 'unsupported' | 'error';

function catchUrl(id: number): string {
    return `${window.location.origin}/catches/${id}`;
}

function shareText(post: ShareableCatch): string {
    return `¡Pesqué ${post.species.name} de ${post.weight_kg} kg! 🎣`;
}

function absoluteImageUrl(post: ShareableCatch): string | null {
    const url = storageUrl(post.image_path);

    if (!url) {
        return null;
    }

    return url.startsWith('http') ? url : `${window.location.origin}${url}`;
}

/** True when the browser can share files — required for Instagram Stories. */
export function canShareImage(): boolean {
    return (
        typeof navigator !== 'undefined' &&
        typeof navigator.share === 'function' &&
        typeof navigator.canShare === 'function'
    );
}

/**
 * Share the catch photo through the native share sheet, where Instagram
 * (Stories/Feed), WhatsApp and other photo apps appear. Falls back to sharing
 * the catch link when file sharing is unavailable.
 */
export async function shareCatchImage(post: ShareableCatch): Promise<ShareResult> {
    const imageUrl = absoluteImageUrl(post);
    const text = shareText(post);

    // Best path: share the actual image file so it can go to an IG Story.
    if (imageUrl && canShareImage()) {
        try {
            const res = await fetch(imageUrl);
            const blob = await res.blob();
            const file = new File([blob], `captura-${post.id}.jpg`, {
                type: blob.type || 'image/jpeg',
            });

            if (navigator.canShare({ files: [file] })) {
                await navigator.share({ files: [file], text, title: text });

                return 'shared';
            }
        } catch (e) {
            if ((e as Error)?.name === 'AbortError') {
                return 'cancelled';
            }
            // Fall through to link sharing.
        }
    }

    // Fallback: share the catch link via the native sheet (no image).
    if (typeof navigator !== 'undefined' && typeof navigator.share === 'function') {
        try {
            await navigator.share({ text, url: catchUrl(post.id), title: text });

            return 'shared';
        } catch (e) {
            return (e as Error)?.name === 'AbortError' ? 'cancelled' : 'error';
        }
    }

    return 'unsupported';
}

/** Open WhatsApp with the catch link prefilled (works on every platform). */
export function shareCatchToWhatsApp(post: ShareableCatch): void {
    const msg = `${shareText(post)} ${catchUrl(post.id)}`;

    window.open(`https://wa.me/?text=${encodeURIComponent(msg)}`, '_blank', 'noopener');
}

/** Copy the public catch link to the clipboard. */
export async function copyCatchLink(post: ShareableCatch): Promise<boolean> {
    try {
        await navigator.clipboard.writeText(catchUrl(post.id));

        return true;
    } catch {
        return false;
    }
}
