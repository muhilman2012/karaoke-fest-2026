<?php

use Livewire\Component;
use App\Models\SpinwheelItem;
use Illuminate\Support\Facades\Cache;

new class extends Component
{
    public $lastTriggerTime = 0;

    public function markAsWinner($id)
    {
        $winner = SpinwheelItem::find($id);
        if ($winner) {
            $winner->update([
                'is_winner' => true,
                'won_at' => now()
            ]);
        }
    }

    public function checkTrigger()
    {
        $trigger = Cache::get('spin_trigger');
        
        if ($trigger && $trigger['time'] > $this->lastTriggerTime) {
            $this->lastTriggerTime = $trigger['time'];
            $this->dispatch('start-spin', [
                'winnerId' => $trigger['winner_id'],
                'duration' => $trigger['duration'],
                'prize' => $trigger['prize'] ?? '' 
            ]);
        }
    }

    public function with(): array
    {
        return [
            'items' => SpinwheelItem::where('is_winner', false)->select('id', 'name')->get(),
            // Mengambil data judul dari Cache
            'eventTitle' => Cache::get('spin_event_title', 'ACARA PUNCAK HUT Ke-81')
        ];
    }
};
?>

<div class="min-h-screen bg-slate-900 overflow-hidden flex flex-col justify-center items-center relative py-10" wire:poll.1s="checkTrigger" x-data="wheelApp({{ $items->toJson() }})">
    
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- JUDUL ACARA DINAMIS -->
    <div class="w-full z-30 text-center mb-8 px-4">
        <h1 class="text-5xl md:text-7xl font-black text-transparent bg-clip-text bg-gradient-to-r from-pink-400 to-indigo-400 drop-shadow-2xl tracking-widest uppercase">
            {{ $eventTitle }}
        </h1>
    </div>

    <!-- AREA RODA -->
    <div wire:ignore class="flex items-center justify-center relative w-full flex-1">
        
        <!-- Pembungkus Roda -->
        <div class="relative flex items-center justify-center" style="width: 75vh; height: 75vh; max-width: 90vw; max-height: 90vw;">
            
            <!-- PENUNJUK DIAMOND -->
            <div class="absolute -right-8 md:-right-12 top-1/2 -translate-y-1/2 z-20 drop-shadow-2xl">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="white" class="drop-shadow-2xl">
                    <path d="M12 2L22 12L12 22L2 12L12 2Z" stroke="#0f172a" stroke-width="2" stroke-linejoin="round"/>
                </svg>
            </div>

            <!-- CANVAS RODA -->
            <div class="relative rounded-full border-[20px] border-white shadow-[0_0_80px_rgba(255,255,255,0.15)] overflow-hidden bg-slate-800 flex items-center justify-center w-full h-full">
                <canvas id="wheelCanvas" width="1200" height="1200" class="w-full h-full object-contain" x-ref="canvas" style="transition-timing-function: cubic-bezier(0.25, 0.1, 0.15, 1);"></canvas>
                
                <!-- PIN TENGAH -->
                <div class="absolute w-16 h-16 bg-white rounded-full shadow-inner z-10 border-8 border-slate-300"></div>
            </div>

        </div>
    </div>

    <!-- AUDIO FILE LOKAL -->
    <audio id="spinSound" loop src="/mp3/drumroll.mp3"></audio>
    <audio id="winSound" src="/mp3/hooray.mp3"></audio>

    <script>
        document.addEventListener('click', function unlockAudio() {
            const s1 = document.getElementById('spinSound');
            const s2 = document.getElementById('winSound');
            s1.play().then(() => s1.pause()).catch(() => {});
            s2.play().then(() => s2.pause()).catch(() => {});
            document.removeEventListener('click', unlockAudio); 
        }, { once: true });

        document.addEventListener('alpine:init', () => {
            Alpine.data('wheelApp', (items) => ({
                items: items,
                isSpinning: false,
                currentRotation: 0,
                
                init() {
                    this.drawWheel();
                    window.addEventListener('start-spin', (e) => {
                        const data = e.detail[0] || e.detail; 
                        this.spinWheel(data.winnerId, data.duration, data.prize);
                    });
                },

                drawWheel() {
                    const canvas = this.$refs.canvas;
                    if(!canvas) return;
                    const ctx = canvas.getContext('2d');
                    const width = canvas.width;
                    const height = canvas.height;
                    const radius = width / 2;
                    
                    ctx.clearRect(0, 0, width, height);
                    ctx.translate(radius, radius);
                    
                    const sliceAngle = (2 * Math.PI) / this.items.length;
                    const colors = ['#f43f5e', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4'];

                    this.items.forEach((item, index) => {
                        ctx.beginPath();
                        ctx.fillStyle = colors[index % colors.length];
                        ctx.moveTo(0, 0);
                        ctx.arc(0, 0, radius, index * sliceAngle, (index + 1) * sliceAngle);
                        ctx.fill();
                        
                        if (this.items.length <= 200) {
                            ctx.save();
                            ctx.rotate(index * sliceAngle + (sliceAngle / 2));
                            ctx.textAlign = "right";
                            ctx.textBaseline = "middle";
                            ctx.fillStyle = "#ffffff";
                            let fontSize = this.items.length > 50 ? 14 : 32; 
                            ctx.font = `bold ${fontSize}px sans-serif`;
                            let displayName = item.name.length > 25 ? item.name.substring(0, 25) + '...' : item.name;
                            ctx.fillText(displayName, radius - 50, 0);
                            ctx.restore();
                        }
                    });
                    ctx.translate(-radius, -radius);
                },

                spinWheel(winnerId, duration, prizeText) {
                    if (this.isSpinning || this.items.length === 0) return;
                    this.isSpinning = true;

                    const winnerIndex = this.items.findIndex(i => i.id === winnerId);
                    const winnerName = this.items[winnerIndex].name;
                    
                    const spinSound = document.getElementById('spinSound');
                    spinSound.currentTime = 0;
                    spinSound.play().catch(e => console.log('Klik layar sekali untuk audio'));

                    const sliceDeg = 360 / this.items.length;
                    const winnerCenterDeg = (winnerIndex * sliceDeg) + (sliceDeg / 2);
                    
                    const extraSpins = 10 * 360; 
                    const targetRotation = this.currentRotation + extraSpins + (360 - winnerCenterDeg) - (this.currentRotation % 360);
                    
                    this.currentRotation = targetRotation;

                    const canvas = this.$refs.canvas;
                    canvas.style.transitionDuration = `${duration}s`;
                    canvas.style.transform = `rotate(${targetRotation}deg)`;

                    setTimeout(() => {
                        spinSound.pause();
                        
                        const winSound = document.getElementById('winSound');
                        winSound.currentTime = 0;
                        winSound.play().catch(e => {});
                        
                        confetti({
                            particleCount: 300,
                            spread: 160,
                            origin: { y: 0.5 },
                            zIndex: 999999, 
                            colors: ['#ff0a54', '#ff477e', '#ff7096', '#ff85a1', '#fbb1bd']
                        });

                        let prizeHtml = prizeText 
                            ? `<div class="text-4xl font-bold text-indigo-400 mt-6">${prizeText}</div>` 
                            : '';

                        Swal.fire({
                            title: '🎉 SELAMAT! 🎉',
                            html: `<div class="text-7xl font-black text-pink-600 mt-6">${winnerName}</div> ${prizeHtml}`,
                            backdrop: `rgba(15, 23, 42, 0.95)`, 
                            showConfirmButton: false, 
                            timer: 12000,
                            timerProgressBar: true,
                            allowOutsideClick: false
                        }).then(() => {
                            this.$wire.markAsWinner(winnerId).then(() => {
                                window.location.reload(); 
                            });
                        });
                    }, duration * 1000);
                }
            }));
        });
    </script>
</div>