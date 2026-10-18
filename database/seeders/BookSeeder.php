<?php

namespace Database\Seeders;

use App\BookStatus;
use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Mengisi rak akun demo dengan buku contoh beserta sampul yang dibuat otomatis.
 */
class BookSeeder extends Seeder
{
    /**
     * Katalog buku contoh.
     *
     * Urutan: judul, penulis, penerbit, tahun, bahasa, jumlah halaman.
     *
     * @var array<int, array{0: string, 1: string, 2: string, 3: int, 4: string, 5: int}>
     */
    private const CATALOG = [
        ['Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, 'id', 529],
        ['Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', 1980, 'id', 535],
        ['Cantik Itu Luka', 'Eka Kurniawan', 'Gramedia Pustaka Utama', 2002, 'id', 520],
        ['Perahu Kertas', 'Dee Lestari', 'Bentang Pustaka', 2009, 'id', 444],
        ['Supernova: Ksatria, Puteri, dan Bintang Jatuh', 'Dee Lestari', 'Truedee Books', 2001, 'id', 456],
        ['Pulang', 'Leila S. Chudori', 'Kepustakaan Populer Gramedia', 2012, 'id', 461],
        ['Negeri 5 Menara', 'Ahmad Fuadi', 'Gramedia Pustaka Utama', 2009, 'id', 423],
        ['Filosofi Teras', 'Henry Manampiring', 'Penerbit Buku Kompas', 2018, 'id', 320],
        ['Laut Bercerita', 'Leila S. Chudori', 'Kepustakaan Populer Gramedia', 2017, 'id', 379],
        ['Rumah Kaca', 'Pramoedya Ananta Toer', 'Hasta Mitra', 1988, 'id', 646],
        ['Sang Pemimpi', 'Andrea Hirata', 'Bentang Pustaka', 2006, 'id', 292],
        ['Perempuan yang Menangis kepada Bulan Hitam', 'Dian Purnomo', 'Gramedia Pustaka Utama', 2020, 'id', 376],
        ['Norwegian Wood', 'Haruki Murakami', 'Kodansha', 1987, 'ja', 296],
        ['Kafka on the Shore', 'Haruki Murakami', 'Shinchosha', 2002, 'ja', 505],
        ['1Q84', 'Haruki Murakami', 'Shinchosha', 2009, 'ja', 1157],
        ['Convenience Store Woman', 'Sayaka Murata', 'Grove Press', 2016, 'ja', 163],
        ['The Midnight Library', 'Matt Haig', 'Canongate Books', 2020, 'en', 304],
        ['Klara and the Sun', 'Kazuo Ishiguro', 'Faber & Faber', 2021, 'en', 320],
        ['Project Hail Mary', 'Andy Weir', 'Ballantine Books', 2021, 'en', 476],
        ['Educated', 'Tara Westover', 'Random House', 2018, 'en', 334],
        ['Atomic Habits', 'James Clear', 'Avery', 2018, 'en', 320],
        ['Thinking, Fast and Slow', 'Daniel Kahneman', 'Farrar, Straus and Giroux', 2011, 'en', 499],
        ['The Design of Everyday Things', 'Don Norman', 'Basic Books', 1988, 'en', 368],
        ['Clean Code', 'Robert C. Martin', 'Prentice Hall', 2008, 'en', 464],
        ['Surely You\'re Joking, Mr. Feynman!', 'Richard P. Feynman', 'W. W. Norton', 1985, 'en', 350],
        ['Shoe Dog', 'Phil Knight', 'Scribner', 2016, 'en', 386],
        ['The Psychology of Money', 'Morgan Housel', 'Harriman House', 2020, 'en', 252],
        ['One Hundred Years of Solitude', 'Gabriel García Márquez', 'Harper & Row', 1967, 'es', 417],
        ['The Little Prince', 'Antoine de Saint-Exupéry', 'Reynal & Hitchcock', 1943, 'fr', 96],
        ['Crime and Punishment', 'Fyodor Dostoevsky', 'The Russian Messenger', 1866, 'ru', 671],
        ['Man\'s Search for Meaning', 'Viktor E. Frankl', 'Beacon Press', 1946, 'de', 165],
        ['The Hobbit', 'J. R. R. Tolkien', 'George Allen & Unwin', 1937, 'en', 310],
    ];

    /**
     * Pola status baca per pemilik rak, diulang mengikuti katalog.
     *
     * @var array<string, array<int, BookStatus>>
     */
    private const STATUS_PATTERNS = [
        'demo' => [BookStatus::Reading, BookStatus::Finished, BookStatus::Finished, BookStatus::WantToRead, BookStatus::Finished],
        'rak-senja' => [BookStatus::Finished, BookStatus::Reading, BookStatus::WantToRead, BookStatus::OnHold, BookStatus::Finished],
        'neon-reader' => [BookStatus::Finished, BookStatus::Finished, BookStatus::Reading, BookStatus::Dropped, BookStatus::Finished],
        'kopi-dan-buku' => [BookStatus::WantToRead, BookStatus::Finished, BookStatus::Reading, BookStatus::Finished, BookStatus::OnHold],
    ];

    /**
     * Warna gradien untuk sampul yang dibuat otomatis.
     *
     * @var array<int, array{0: string, 1: string}>
     */
    private const COVER_PALETTE = [
        ['#1e3a8a', '#0f172a'],
        ['#7c2d12', '#1c1917'],
        ['#065f46', '#022c22'],
        ['#831843', '#1f0713'],
        ['#4c1d95', '#1e1b4b'],
        ['#0e7490', '#082f49'],
        ['#92400e', '#292524'],
        ['#374151', '#111827'],
        ['#9d174d', '#4c0519'],
        ['#155e75', '#164e63'],
        ['#365314', '#1a2e05'],
        ['#5b21b6', '#2e1065'],
    ];

    /**
     * Paragraf pengisi untuk sinopsis dan ulasan contoh.
     *
     * Sengaja tidak memakai helper `fake()`: helper itu hanya terdefinisi bila
     * `Faker\Factory` tersedia (lihat helpers.php milik framework), sedangkan
     * fakerphp/faker ada di require-dev dan image produksi dibangun dengan
     * `composer install --no-dev`. Pemilihan paragraf di sini deterministik
     * lewat crc32 judul, jadi isi demo tidak berubah antar-run.
     *
     * @var array<int, string>
     */
    private const FILLER_PARAGRAPHS = [
        'Buku ini punya tempo yang enak diikuti. Bab-babnya pendek, jadi terasa ringan dibaca sambil lalu, tapi ide yang dibawa tetap melekat setelah ditutup. Beberapa bagian bahkan sengaja aku baca ulang untuk menikmati detailnya.',
        'Yang paling kena buatku adalah caranya membangun suasana. Detail kecil—cuaca, kebiasaan tokoh, pilihan kata—dibuat cukup rapat sampai rasanya ikut duduk di ruangan yang sama. Tidak banyak buku yang bisa menjaga konsistensi seperti ini sampai halaman terakhir.',
        'Struktur ceritanya rapi: setiap bagian punya alasan untuk ada, dan tidak ada bab yang terasa cuma tempelan. Konfliknya tidak selalu besar, tapi selalu punya taruhan yang jelas, jadi tetap ingin dibaca terus.',
        'Kalau kamu tipe yang suka mencatat kutipan, siapkan pensil. Ada cukup banyak kalimat yang rasanya sayang kalau dilewatkan. Aku sendiri menandai beberapa halaman yang ingin aku buka lagi nanti.',
        'Buku ini cocok dibaca pelan. Bukan karena sulit, tapi karena banyak hal kecil yang mudah terlewat kalau diburu-buru. Aku menamatkannya di akhir pekan, dan setelah itu butuh jeda sebelum pindah ke judul berikutnya.',
        'Bagi pembaca yang baru menyukai tema seperti ini, buku tersebut jadi pintu masuk yang bersahabat. Bahasanya tidak berbelit, dan istilah yang muncul dijelaskan tanpa menggurui. Untuk pembaca lama pun masih ada lapisan yang bisa dinikmati.',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()
            ->whereIn('username', array_keys(self::STATUS_PATTERNS))
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        $catalogIndex = 0;

        foreach ($users as $user) {
            $pattern = self::STATUS_PATTERNS[$user->username] ?? [BookStatus::Finished];

            foreach (self::CATALOG as $entry) {
                $status = $pattern[$catalogIndex % count($pattern)];
                $catalogIndex++;

                // Setiap pemilik rak memakai sebagian katalog saja.
                if (($catalogIndex + $user->id) % 3 === 0 && $status !== BookStatus::Reading) {
                    continue;
                }

                $book = Book::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'title' => $entry[0],
                    ],
                    [
                        'author' => $entry[1],
                        'publisher' => $entry[2],
                        'published_year' => $entry[3],
                        'language' => $entry[4],
                        'total_pages' => $entry[5],
                        'status' => $status,
                        'rating' => $status === BookStatus::Finished ? $this->ratingFor($entry[0]) : null,
                        'synopsis' => $this->synopsisFor($entry[0], $entry[1]),
                        'review' => $status === BookStatus::Finished ? $this->reviewFor($entry[0]) : null,
                        'is_favorite' => $catalogIndex % 7 === 0,
                        'started_at' => $status === BookStatus::WantToRead ? null : now()->subDays(60 - ($catalogIndex % 40)),
                        'finished_at' => $status === BookStatus::Finished ? now()->subDays(30 - ($catalogIndex % 25)) : null,
                    ],
                );

                $this->attachGeneratedCover($book, $user);
            }
        }
    }

    /**
     * Rating deterministik supaya data demo selalu masuk akal.
     */
    private function ratingFor(string $title): int
    {
        return 3 + (crc32($title) % 3);
    }

    /**
     * Sinopsis versi pemilik rak.
     */
    private function synopsisFor(string $title, string $author): string
    {
        return implode("\n\n", [
            "Catatan pembuka: \"{$title}\" karya {$author} adalah salah satu buku yang paling sering aku rekomendasikan ke teman dekat.",
            ...$this->fillerFor($title, 2),
        ]);
    }

    /**
     * Review versi pemilik rak.
     */
    private function reviewFor(string $title): string
    {
        return implode("\n\n", [
            ...$this->fillerFor($title, 1, offset: 2),
            "Kalau kamu cuma punya waktu buat satu buku bulan ini, \"{$title}\" pilihan yang aman.",
        ]);
    }

    /**
     * Ambil paragraf pengisi deterministik berdasarkan judul.
     *
     * Offset dipakai supaya paragraf di ulasan tidak mengulang paragraf
     * pertama di sinopsis untuk judul yang sama.
     *
     * @return array<int, string>
     */
    private function fillerFor(string $title, int $count, int $offset = 0): array
    {
        $seed = crc32($title);
        $total = count(self::FILLER_PARAGRAPHS);

        $paragraphs = [];

        for ($index = 0; $index < $count; $index++) {
            $paragraphs[] = self::FILLER_PARAGRAPHS[($seed + $offset + $index) % $total];
        }

        return $paragraphs;
    }

    /**
     * Buat sampul depan sederhana (SVG) agar rak demo tidak tampil kosong.
     */
    private function attachGeneratedCover(Book $book, User $user): void
    {
        $palette = self::COVER_PALETTE[crc32($book->title) % count(self::COVER_PALETTE)];
        $lines = $this->wrap($book->title, 16, 4);

        $textLines = '';
        $offset = 250;

        foreach ($lines as $index => $line) {
            $y = $offset + ($index * 54);
            $textLines .= '<text x="40" y="'.$y.'" font-family="Georgia, serif" font-size="42" font-weight="700" fill="#f8fafc">'.$this->escape($line).'</text>';
        }

        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="400" height="600" viewBox="0 0 400 600" role="img" aria-label="{$this->escape($book->title)}">
            <defs>
                <linearGradient id="cover" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="{$palette[0]}"/>
                    <stop offset="100%" stop-color="{$palette[1]}"/>
                </linearGradient>
            </defs>
            <rect width="400" height="600" fill="url(#cover)"/>
            <rect x="0" y="0" width="14" height="600" fill="rgba(0,0,0,0.35)"/>
            <rect x="34" y="120" width="86" height="6" fill="rgba(255,255,255,0.55)"/>
            {$textLines}
            <text x="40" y="520" font-family="Helvetica, Arial, sans-serif" font-size="22" fill="rgba(248,250,252,0.85)">{$this->escape($book->author)}</text>
            <text x="40" y="556" font-family="Helvetica, Arial, sans-serif" font-size="18" fill="rgba(248,250,252,0.6)">{$this->escape((string) $book->publisher)}</text>
        </svg>
        SVG;

        $path = 'covers/demo/'.$user->username.'-'.$book->id.'.svg';

        Storage::disk(config('bookshelf.cover.disk'))->put($path, $svg);

        $book->forceFill(['cover_path' => $path])->save();
    }

    /**
     * Pecah judul jadi beberapa baris agar muat di sampul.
     *
     * @return array<int, string>
     */
    private function wrap(string $text, int $maxChars, int $maxLines): array
    {
        $words = preg_split('/\s+/u', $text) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current.' '.$word;

            if (mb_strlen($candidate) <= $maxChars) {
                $current = $candidate;

                continue;
            }

            if ($current !== '') {
                $lines[] = $current;
            }

            $current = $word;
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return array_slice($lines, 0, $maxLines);
    }

    /**
     * Escape teks untuk SVG.
     */
    private function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
