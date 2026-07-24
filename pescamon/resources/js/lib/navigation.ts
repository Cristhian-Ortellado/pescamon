import { router } from '@inertiajs/vue3';

/**
 * Return to the previous view.
 *
 * Uses the browser/Inertia history so the user goes back to wherever they
 * actually came from, instead of a hard-coded route. Falls back to `fallback`
 * when there is no in-app history to return to (e.g. the page was opened via a
 * direct link or a fresh tab).
 */
export function goBack(fallback = '/dashboard'): void {
    if (typeof window !== 'undefined' && window.history.length > 1) {
        window.history.back();
    } else {
        router.visit(fallback);
    }
}
