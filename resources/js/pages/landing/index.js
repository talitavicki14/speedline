const HeroCarousel = {
    slides: [],
    dots: [],
    current: 0,
    timer: null,
    interval: 4500,

    init() {
        this.slides = document.querySelectorAll('.hero-slide');
        this.dots   = document.querySelectorAll('.hero-dot');
        if (this.slides.length < 2) return;

        this.startAuto();

        window.heroCarouselMove = (dir) => {
            this.stopAuto();
            this.show(this.current + dir);
            this.startAuto();
        };

        window.heroCarouselGoTo = (idx) => {
            this.stopAuto();
            this.show(idx);
            this.startAuto();
        };
    },

    show(idx) {
        if (!this.slides.length) return;

        const next = (idx + this.slides.length) % this.slides.length;
        if (next === this.current) return;

        const currentSlide = this.slides[this.current];
        const targetSlide  = this.slides[next];

        this.slides.forEach((slide, i) => {
            if (i !== this.current && i !== next) {
                slide.classList.remove('z-10', 'z-20', 'opacity-100');
                slide.classList.add('z-0', 'opacity-0');
            }
        });

        currentSlide.classList.remove('z-20');
        currentSlide.classList.add('z-10', 'opacity-100');

        targetSlide.classList.remove('z-0', 'z-10', 'opacity-0');
        targetSlide.classList.add('z-20', 'opacity-100');

        if (this.dots[this.current]) {
            this.dots[this.current].classList.remove('w-8', 'bg-white');
            this.dots[this.current].classList.add('w-2.5', 'bg-white/40', 'hover:bg-white/60');
        }
        if (this.dots[next]) {
            this.dots[next].classList.remove('w-2.5', 'bg-white/40', 'hover:bg-white/60');
            this.dots[next].classList.add('w-8', 'bg-white');
        }

        clearTimeout(this.transitionTimer);
        this.transitionTimer = setTimeout(() => {
            targetSlide.classList.remove('z-20');
            targetSlide.classList.add('z-10');

            currentSlide.classList.remove('z-10', 'opacity-100');
            currentSlide.classList.add('z-0', 'opacity-0');
        }, 700);

        this.current = next;
    },

    startAuto() {
        this.stopAuto();
        this.timer = setInterval(() => this.show(this.current + 1), this.interval);
    },

    stopAuto() {
        if (this.timer) clearInterval(this.timer);
    }
};

document.addEventListener('DOMContentLoaded', () => HeroCarousel.init());
