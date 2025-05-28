<?php

namespace Database\Seeders;

use App\Models\LibraryBranch;
use Illuminate\Database\Seeder;

class LibraryBranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'branch_name' => 'FIS Summit Branch',
                'address' => 'XVQ4+8M,Summit condominium Addis Ababa',
                'contact_number' => '+251911234567',
                'email' => 'main.library@example.com',
                'location' => '<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d126107.16519985234!2d38.6298757!3d8.9860472!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x164b9b778ba53e33%3A0x9ce38d58231fa8ce!2zRmxpcHBlciBJbnRlcm5hdGlvbmFsIFNjaG9vbCAtIFN1bW1pdCBDb25kb21pbml1bSB8IOGNjeGIiuGNkOGIrSDhiqLhipXhibDhiK3hipPhiL3hipPhiI0g4Ym14Yid4YiF4Yit4Ym1IOGJpOGJtSDhiLDhiJrhibUg4Yqu4YqV4Yu24Yia4YqS4Yuo4YidIOGJheGIreGKleGMq-GNjQ!5e0!3m2!1sen!2set!4v1748333292816!5m2!1sen!2set" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
                'library_time' => json_encode([
                    'monday' => ['open' => '08:00', 'close' => '20:00'],
                    'tuesday' => ['open' => '08:00', 'close' => '20:00'],
                    'wednesday' => ['open' => '08:00', 'close' => '20:00'],
                    'thursday' => ['open' => '08:00', 'close' => '20:00'],
                    'friday' => ['open' => '08:00', 'close' => '18:00'],
                    'saturday' => ['open' => '09:00', 'close' => '16:00'],
                    'sunday' => ['open' => '12:00', 'close' => '16:00'],
                ]),
            ],
            [
                'branch_name' => 'FIS Beklobet Branch',
                'address' => 'Beklobet, Addis Ababa',
                'contact_number' => '+251922345678',
                'email' => 'science.library@example.com',
                'location' => ' <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d126107.16519985234!2d38.6298757!3d8.9860472!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x164b844acde93531%3A0xb218aabe1403e29c!2zRmxpcHBlciBJbnRlcm5hdGlvbmFsIFNjaG9vbCB8IEJla2xvYmV0IHwg4Y2K4YiK4Y2Q4YitIOGKouGKleGJsOGIreGKk-GIveGKk-GIjSDhibUv4Ymk4Ym1IHwg4Ymg4YmF4YiOIOGJpOGJtQ!5e0!3m2!1sen!2set!4v1748365580831!5m2!1sen!2set" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
                'library_time' => json_encode([
                    'monday' => ['open' => '08:30', 'close' => '19:00'],
                    'tuesday' => ['open' => '08:30', 'close' => '19:00'],
                    'wednesday' => ['open' => '08:30', 'close' => '19:00'],
                    'thursday' => ['open' => '08:30', 'close' => '19:00'],
                    'friday' => ['open' => '08:30', 'close' => '17:00'],
                    'saturday' => ['open' => '10:00', 'close' => '15:00'],
                    'sunday' => ['open' => '00:00', 'close' => '00:00'],
                ]),
            ],
            [
                'branch_name' => 'FIS Summit Square Branch',
                'address' => '2R2X+VFH Summit condominium, Addis Ababa',
                'contact_number' => '+251922347658',
                'email' => 'kg.library@example.com',
                'location' => '<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d126107.16519985234!2d38.6298757!3d8.9860472!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x164b9b7a0b58cfd9%3A0x309609e38907967f!2sFlippers%20International%20School%20%7C%20KG%20%7C%20Summit%20Square!5e0!3m2!1sen!2set!4v1748366728565!5m2!1sen!2set" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>',
                'library_time' => json_encode([
                    'monday' => ['open' => '08:30', 'close' => '19:00'],
                    'tuesday' => ['open' => '08:30', 'close' => '19:00'],
                    'wednesday' => ['open' => '08:30', 'close' => '19:00'],
                    'thursday' => ['open' => '08:30', 'close' => '19:00'],
                    'friday' => ['open' => '08:30', 'close' => '17:00'],
                    'saturday' => ['open' => '10:00', 'close' => '15:00'],
                    'sunday' => ['open' => '00:00', 'close' => '00:00'],
                ]),
            ],
        ];

        foreach ($branches as $branch) {
            LibraryBranch::firstOrCreate(
                ['branch_name' => $branch['branch_name']],
                $branch
            );
        }

        $this->command->info('Library branches seeded successfully.');
    }
}
