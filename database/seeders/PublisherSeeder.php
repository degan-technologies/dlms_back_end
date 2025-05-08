<?php

namespace Database\Seeders;

use App\Models\Publisher;
use Illuminate\Database\Seeder;

class PublisherSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    // Create common publishers for library items
    $publishers = [
      [
        'publisher_name' => 'Penguin Random House',
        'address' => '1745 Broadway, New York, NY 10019, USA',
        'contact_info' => 'info@penguinrandomhouse.com',
      ],
      [
        'publisher_name' => 'HarperCollins',
        'address' => '195 Broadway, New York, NY 10007, USA',
        'contact_info' => 'info@harpercollins.com',
      ],
      [
        'publisher_name' => 'Simon & Schuster',
        'address' => '1230 Avenue of the Americas, New York, NY 10020, USA',
        'contact_info' => 'info@simonandschuster.com',
      ],
      [
        'publisher_name' => 'Macmillan Publishers',
        'address' => '120 Broadway, New York, NY 10271, USA',
        'contact_info' => 'info@macmillan.com',
      ],
      [
        'publisher_name' => 'Oxford University Press',
        'address' => 'Great Clarendon Street, Oxford, OX2 6DP, UK',
        'contact_info' => 'info@oup.com',
      ],
      [
        'publisher_name' => 'Bloomsbury Publishing',
        'address' => '50 Bedford Square, London, WC1B 3DP, UK',
        'contact_info' => 'info@bloomsbury.com',
      ],
      [
        'publisher_name' => 'Scholastic',
        'address' => '557 Broadway, New York, NY 10012, USA',
        'contact_info' => 'info@scholastic.com',
      ],
      [
        'publisher_name' => 'Wiley',
        'address' => '111 River Street, Hoboken, NJ 07030, USA',
        'contact_info' => 'info@wiley.com',
      ],
      [
        'publisher_name' => 'MIT Press',
        'address' => '1 Rogers Street, Cambridge, MA 02142, USA',
        'contact_info' => 'info@mitpress.mit.edu',
      ],
      [
        'publisher_name' => 'Packt Publishing',
        'address' => 'Livery Place, 35 Livery Street, Birmingham, B3 2PB, UK',
        'contact_info' => 'info@packtpub.com',
      ],
    ];

    foreach ($publishers as $publisher) {
      Publisher::updateOrCreate(
        ['publisher_name' => $publisher['publisher_name']],
        $publisher
      );
    }
  }
}