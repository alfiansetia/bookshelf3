import { onBeforeUnmount, onMounted, type Ref } from 'vue';

/**
 * Geser kontainer horizontal dengan drag mouse (seperti galeri foto).
 * Sentuh tetap pakai scroll native; hanya pointer mouse yang diambil alih.
 * Klik setelah drag jauh otomatis dibatalkan supaya kartu Link
 * tidak ikut terbuka saat pengguna bermaksud menggeser.
 */
export function useDragScroll(target: Ref<HTMLElement | null>): void {
    const threshold = 6;

    let startX = 0;
    let startScroll = 0;
    let dragging = false;
    let moved = false;

    function onPointerDown(event: PointerEvent): void {
        if (event.pointerType !== 'mouse' || event.button !== 0) {
            return;
        }

        const element = target.value;

        if (!element || element.scrollWidth <= element.clientWidth + 1) {
            return;
        }

        dragging = true;
        moved = false;
        startX = event.clientX;
        startScroll = element.scrollLeft;

        element.style.scrollSnapType = 'none';
        element.classList.add('select-none');

        window.addEventListener('pointermove', onPointerMove);
        window.addEventListener('pointerup', onPointerUp, { once: true });
        window.addEventListener('pointercancel', onPointerUp, { once: true });
    }

    function onPointerMove(event: PointerEvent): void {
        const element = target.value;

        if (!dragging || !element) {
            return;
        }

        const distance = event.clientX - startX;

        if (!moved && Math.abs(distance) <= threshold) {
            return;
        }

        moved = true;
        element.scrollLeft = startScroll - distance;
        event.preventDefault();
    }

    function onPointerUp(): void {
        dragging = false;

        const element = target.value;

        element?.style.removeProperty('scroll-snap-type');
        element?.classList.remove('select-none');
        window.removeEventListener('pointermove', onPointerMove);
    }

    function onClickCapture(event: Event): void {
        if (moved) {
            event.preventDefault();
            event.stopPropagation();
            moved = false;
        }
    }

    function onDragStart(event: Event): void {
        event.preventDefault();
    }

    onMounted(() => {
        const element = target.value;

        element?.addEventListener('pointerdown', onPointerDown);
        element?.addEventListener('click', onClickCapture, true);
        element?.addEventListener('dragstart', onDragStart);
    });

    onBeforeUnmount(() => {
        const element = target.value;

        element?.removeEventListener('pointerdown', onPointerDown);
        element?.removeEventListener('click', onClickCapture, true);
        element?.removeEventListener('dragstart', onDragStart);
        window.removeEventListener('pointermove', onPointerMove);
    });
}
