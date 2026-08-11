import Alpine from 'alpinejs';
window.Alpine = Alpine;

const createNotificationSound = () => {
    let audio = null;
    let unlocked = false;
    let lastPlayedAt = 0;

    const getAudio = () => {
        if (!audio) {
            audio = new Audio('/sounds/notification.mp3');
            audio.preload = 'auto';
            audio.volume = 0.75;
        }

        return audio;
    };

    const unlock = () => {
        const sound = getAudio();
        unlocked = true;
        sound.muted = true;

        const playPromise = sound.play();
        if (playPromise?.then) {
            playPromise
                .then(() => {
                    sound.pause();
                    sound.currentTime = 0;
                    sound.muted = false;
                })
                .catch(() => {
                    sound.muted = false;
                    sound.load();
                });
            return;
        }

        sound.muted = false;
    };

    ['click', 'keydown', 'touchstart'].forEach((eventName) => {
        window.addEventListener(eventName, unlock, { once: true, passive: true });
    });

    return {
        play() {
            const now = Date.now();
            if (now - lastPlayedAt < 1500) {
                return;
            }

            lastPlayedAt = now;
            const sound = getAudio();
            sound.currentTime = 0;

            const playPromise = sound.play();
            if (playPromise?.catch) {
                playPromise.catch(() => {
                    unlocked = false;
                });
            }
        },

        prime() {
            if (unlocked) {
                getAudio().load();
            }
        },
    };
};

window.notificationSound = window.notificationSound || createNotificationSound();

Alpine.start();
