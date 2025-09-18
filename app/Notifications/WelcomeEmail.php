<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Selamat Datang di Sistem Absensi SMKN 1 Punggelan')
            ->greeting('Selamat datang, ' . $notifiable->name . '!')
            ->line('Akun Anda telah disetujui oleh administrator.')
            ->line('Anda sekarang dapat mengakses Sistem Absensi SMKN 1 Punggelan dengan fitur-fitur berikut:')
            ->line('• Pencatatan kehadiran otomatis')
            ->line('• Pengajuan cuti dan izin')
            ->line('• Laporan kehadiran')
            ->line('• Manajemen jadwal kerja')
            ->action('Masuk ke Sistem', url('/admin'))
            ->line('Silakan login dengan email dan password yang telah Anda daftarkan.')
            ->line('Jika Anda memiliki pertanyaan, jangan ragu untuk menghubungi administrator.')
            ->salutation('Salam, Tim Sistem Absensi SMKN 1 Punggelan');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}