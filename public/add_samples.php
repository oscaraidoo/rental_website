<?php
/**
 * Sample Properties Loader — production safe, self-hosted images
 * Access: https://rent.habellesfoundation.org/add_samples.php?key=habelles2026
 *
 * What it does:
 *  - Creates landlord profile for the admin user (id=1) if missing
 *  - Inserts 15 verified sample properties with full specs, spaces and photos
 *  - DOWNLOADS each photo from Unsplash into public/uploads/properties/images/
 *    and stores LOCAL paths ('uploads/...') in property_images, so images are
 *    served from your own domain and survive online permanently (no hotlinking).
 *  - Idempotent: re-running skips titles that already exist (use ?force=1 to
 *    wipe previous sample properties first)
 *
 * DELETE this file immediately after running.
 */

// ── Secret key guard ─────────────────────────────────────────
if (($_GET['key'] ?? '') !== 'habelles2026') {
    http_response_code(403);
    die('Forbidden.');
}
$force = isset($_GET['force']);

if (!defined('ROOT_PATH')) { define('ROOT_PATH', dirname(__DIR__)); }
if (!defined('DS')) { define('DS', DIRECTORY_SEPARATOR); }

if (file_exists(ROOT_PATH . '/.env')) {
    foreach (file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v);
    }
}

require ROOT_PATH . '/bootstrap/app.php';

use App\Helpers\Database;
use App\Models\PropertyModel;

// ── Ensure landlord row for admin user (id=1) ────────────────
$landlordId = Database::fetchColumn("SELECT id FROM landlords WHERE user_id = 1");
if (!$landlordId) {
    Database::query("INSERT IGNORE INTO landlords (user_id, verified) VALUES (1, 1)");
    $landlordId = Database::fetchColumn("SELECT id FROM landlords WHERE user_id = 1");
}
if (!$landlordId) {
    http_response_code(500);
    die('Could not create landlord profile for user id=1.');
}
$landlordId = (int) $landlordId;
$adminId    = 1;

$imgDirAbs  = ROOT_PATH . '/public/uploads/properties/images';
if (!is_dir($imgDirAbs)) {
    @mkdir($imgDirAbs, 0755, true);
}

// ── Photo sets (Unsplash source URLs) ────────────────────────
$photoSets = [
    0  => ['https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=1200&q=80&auto=format&fit=crop'],
    1  => ['https://images.unsplash.com/photo-1560185007-cde436f6a4d0?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1567767292278-a4f21aa2d36e?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1616594039964-ae9021a400a0?w=1200&q=80&auto=format&fit=crop'],
    2  => ['https://images.unsplash.com/photo-1484154218962-a197022b5858?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=1200&q=80&auto=format&fit=crop'],
    3  => ['https://images.unsplash.com/photo-1631049552057-403cdb8f0658?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1540518614846-7eded433c457?w=1200&q=80&auto=format&fit=crop'],
    4  => ['https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1583608205776-bfd35f0d9f83?w=1200&q=80&auto=format&fit=crop'],
    5  => ['https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1554995207-c18c203602cb?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1598928506311-c55ded91a20c?w=1200&q=80&auto=format&fit=crop'],
    6  => ['https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1200&q=80&from=format&fit=crop','https://images.unsplash.com/photo-1523217582562-09d0def993a6?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=1200&q=80&auto=format&fit=crop'],
    7  => ['https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1536376072261-38c75010e6c9?w=1200&q=80&auto=format&fit=crop'],
    8  => ['https://images.unsplash.com/photo-1497366216548-37526070297c?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?w=1200&q=80&auto=format&fit=crop'],
    9  => ['https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=1200&q=80&auto=format&auto=format&fit=crop','https://images.unsplash.com/photo-1585412727339-54e4bae3bbf9?w=1200&q=80&auto=format&fit=crop'],
    10 => ['https://images.unsplash.com/photo-1505873242700-f289a29e1724?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1487700160041-babef9c3cb55?w=1200&q=80&auto=format&fit=crop'],
    11 => ['https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1572120360610-d971b9d7767c?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1556228720-195a672e8a03?w=1200&q=80&auto=format&fit=crop'],
    12 => ['https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1617806118233-18e1de247200?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1616047006789-b7af5afb8c20?w=1200&q=80&auto=format&fit=crop'],
    13 => ['https://images.unsplash.com/photo-1631049552057-403cdb8f0658?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1484101403633-562f891dc89a?w=1200&q=80&auto=format&fit=crop'],
    14 => ['https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1484154218962-a197022b5858?w=1200&q=80&auto=format&fit=crop','https://images.unsplash.com/photo-1567767292278-a4f21aa2d36e?w=1200&q=80&auto=format&fit=crop'],
];

$cats = ['exterior','living_room','bedroom','kitchen','bathroom','other'];

// ── Property definitions ──────────────────────────────────────
$samples = [
    ['title'=>'1-Bedroom Self-Contained at Adenta Housing','description'=>'Neat and spacious 1-bedroom self-contained in a quiet residential area. Tile floors, quality ceiling, own bathroom and kitchen.','type_id'=>2,'region_id'=>1,'city_id'=>1,'rent'=>1200,'deposit'=>1200,'bedrooms'=>1,'bathrooms'=>1,'toilets'=>1,'kitchens'=>1,'self'=>1,'parking'=>0,'water'=>1,'electric'=>'prepaid','furnished'=>'unfurnished','level'=>'ground','walk'=>5,'vehicle'=>1,'road'=>'tarred','gated'=>1,'guard'=>0,'fenced'=>1,'community'=>'Adenta Housing','street'=>'Housing Road','featured'=>1,'photos'=>0,'spaces'=>[['bedroom','Bedroom',3.8,3.2,2.7,'m'],['bathroom','Bathroom',2.0,1.5,2.7,'m'],['kitchen','Kitchen',2.5,2.0,2.7,'m']]],
    ['title'=>'2-Bedroom Apartment at East Legon','description'=>'Modern 2-bedroom apartment in one of Accra\'s most sought-after neighbourhoods. 24/7 water and prepaid electricity.','type_id'=>5,'region_id'=>1,'city_id'=>1,'rent'=>3500,'deposit'=>3500,'bedrooms'=>2,'bathrooms'=>2,'toilets'=>2,'kitchens'=>1,'self'=>1,'parking'=>1,'water'=>1,'electric'=>'prepaid','furnished'=>'semi_furnished','level'=>'first','walk'=>3,'vehicle'=>1,'road'=>'tarred','gated'=>1,'guard'=>1,'fenced'=>1,'community'=>'East Legon','street'=>'American House Road','featured'=>1,'photos'=>1,'spaces'=>[['bedroom','Master Bedroom',4.5,3.8,2.9,'m'],['bedroom','Bedroom 2',3.8,3.2,2.9,'m'],['living_room','Living Room',5.5,4.2,2.9,'m'],['kitchen','Kitchen',3.2,2.8,2.9,'m']]],
    ['title'=>'Chamber & Hall Self-Contained at Kasoa','description'=>'Clean chamber and hall with own bathroom and kitchen in a gated compound at Kasoa. Easy access to Accra-Cape Coast highway.','type_id'=>4,'region_id'=>1,'city_id'=>3,'rent'=>600,'deposit'=>600,'bedrooms'=>1,'bathrooms'=>1,'toilets'=>1,'kitchens'=>1,'self'=>1,'parking'=>0,'water'=>1,'electric'=>'self_meter','furnished'=>'unfurnished','level'=>'ground','walk'=>7,'vehicle'=>1,'road'=>'tarred','gated'=>1,'guard'=>0,'fenced'=>1,'community'=>'Kasoa New Market','street'=>'Winneba Road','featured'=>0,'photos'=>2,'spaces'=>[['bedroom','Bedroom',3.5,3.0,2.7,'m'],['living_room','Hall',4.0,3.5,2.7,'m']]],
    ['title'=>'Single Room at Madina','description'=>'Affordable single room in a well-maintained compound at Madina. Very close to Madina market and transport.','type_id'=>1,'region_id'=>1,'city_id'=>5,'rent'=>280,'deposit'=>280,'bedrooms'=>1,'bathrooms'=>0,'toilets'=>0,'kitchens'=>0,'self'=>0,'parking'=>0,'water'=>1,'electric'=>'shared_meter','furnished'=>'unfurnished','level'=>'ground','walk'=>4,'vehicle'=>0,'road'=>'gravel','gated'=>0,'guard'=>0,'fenced'=>1,'community'=>'Madina Zongo','street'=>'Abuja Road','featured'=>0,'photos'=>3,'spaces'=>[['bedroom','Room',3.2,2.8,2.6,'m']]],
    ['title'=>'3-Bedroom House at Tema Community 25','description'=>'Spacious 3-bedroom detached house with large compound, parking and garden. Prepaid electricity, borehole water.','type_id'=>7,'region_id'=>1,'city_id'=>2,'rent'=>4200,'deposit'=>4200,'bedrooms'=>3,'bathrooms'=>2,'toilets'=>3,'kitchens'=>1,'self'=>1,'parking'=>1,'water'=>1,'electric'=>'prepaid','furnished'=>'unfurnished','level'=>'ground','walk'=>6,'vehicle'=>1,'road'=>'tarred','gated'=>1,'guard'=>0,'fenced'=>1,'community'=>'Community 25','street'=>'Comm 25 Ring Road','featured'=>1,'photos'=>4,'spaces'=>[['bedroom','Master Bedroom',5.0,4.2,3.0,'m'],['bedroom','Bedroom 2',4.0,3.5,3.0,'m'],['bedroom','Bedroom 3',3.8,3.2,3.0,'m'],['living_room','Living Room',6.5,5.0,3.0,'m'],['kitchen','Kitchen',3.5,3.0,3.0,'m']]],
    ['title'=>'Studio Apartment at Osu','description'=>'Compact fully furnished studio in the heart of Osu. Walking distance to Oxford Street restaurants and shops.','type_id'=>11,'region_id'=>1,'city_id'=>1,'rent'=>2200,'deposit'=>2200,'bedrooms'=>0,'bathrooms'=>1,'toilets'=>1,'kitchens'=>1,'self'=>1,'parking'=>0,'water'=>1,'electric'=>'prepaid','furnished'=>'furnished','level'=>'first','walk'=>2,'vehicle'=>1,'road'=>'tarred','gated'=>1,'guard'=>1,'fenced'=>1,'community'=>'Osu','street'=>'Cantonments Road','featured'=>0,'photos'=>5,'spaces'=>[['living_room','Studio Space',6.0,4.5,2.8,'m'],['bathroom','Bathroom',2.5,1.8,2.8,'m']]],
    ['title'=>'2-Bedroom Self-Contained at Kumasi Ahodwo','description'=>'Beautiful 2-bedroom self-contained in the prestigious Ahodwo area of Kumasi. Tiled floors, 24/7 GWCL water.','type_id'=>6,'region_id'=>2,'city_id'=>6,'rent'=>1800,'deposit'=>1800,'bedrooms'=>2,'bathrooms'=>2,'toilets'=>2,'kitchens'=>1,'self'=>1,'parking'=>1,'water'=>1,'electric'=>'prepaid','furnished'=>'unfurnished','level'=>'ground','walk'=>5,'vehicle'=>1,'road'=>'tarred','gated'=>1,'guard'=>0,'fenced'=>1,'community'=>'Ahodwo','street'=>'Ahodwo Road','featured'=>1,'photos'=>6,'spaces'=>[['bedroom','Master Bedroom',4.2,3.6,2.8,'m'],['bedroom','Bedroom 2',3.8,3.2,2.8,'m'],['living_room','Living/Dining',5.0,4.0,2.8,'m'],['kitchen','Kitchen',2.8,2.4,2.8,'m']]],
    ['title'=>'Single Room Self-Contained at Kumasi Suame','description'=>'Neat single room self-contained at Suame, Kumasi. Own bathroom, toilet and kitchen. Close to Suame Magazine.','type_id'=>2,'region_id'=>2,'city_id'=>6,'rent'=>450,'deposit'=>450,'bedrooms'=>1,'bathrooms'=>1,'toilets'=>1,'kitchens'=>1,'self'=>1,'parking'=>0,'water'=>1,'electric'=>'shared_meter','furnished'=>'unfurnished','level'=>'ground','walk'=>8,'vehicle'=>1,'road'=>'gravel','gated'=>0,'guard'=>0,'fenced'=>1,'community'=>'Suame','street'=>'Suame Round Road','featured'=>0,'photos'=>7,'spaces'=>[['bedroom','Room',3.5,3.0,2.7,'m'],['bathroom','Bathroom',1.8,1.5,2.7,'m']]],
    ['title'=>'3-Bedroom Apartment at Kumasi Nhyiaeso','description'=>'Tastefully finished 3-bedroom apartment at Nhyiaeso. Good ventilation, parking and reliable GWCL water.','type_id'=>7,'region_id'=>2,'city_id'=>6,'rent'=>3200,'deposit'=>3200,'bedrooms'=>3,'bathrooms'=>2,'toilets'=>2,'kitchens'=>1,'self'=>1,'parking'=>1,'water'=>1,'electric'=>'prepaid','furnished'=>'semi_furnished','level'=>'first','walk'=>4,'vehicle'=>1,'road'=>'tarred','gated'=>1,'guard'=>1,'fenced'=>1,'community'=>'Nhyiaeso','street'=>'Nhyiaeso Avenue','featured'=>0,'photos'=>8,'spaces'=>[['bedroom','Master Bedroom',4.8,4.0,2.9,'m'],['bedroom','Bedroom 2',4.0,3.5,2.9,'m'],['bedroom','Bedroom 3',3.8,3.0,2.9,'m'],['living_room','Living Room',6.0,4.8,2.9,'m']]],
    ['title'=>'1-Bedroom Apartment at Takoradi Effia','description'=>'Attractive 1-bedroom apartment at Effia, Takoradi. 7 minutes walk to main road. Own bathroom and kitchen.','type_id'=>5,'region_id'=>3,'city_id'=>8,'rent'=>900,'deposit'=>900,'bedrooms'=>1,'bathrooms'=>1,'toilets'=>1,'kitchens'=>1,'self'=>1,'parking'=>0,'water'=>1,'electric'=>'prepaid','furnished'=>'unfurnished','level'=>'ground','walk'=>7,'vehicle'=>1,'road'=>'tarred','gated'=>1,'guard'=>0,'fenced'=>1,'community'=>'Effia','street'=>'Effia Road','featured'=>1,'photos'=>9,'spaces'=>[['bedroom','Bedroom',3.8,3.2,2.7,'m'],['living_room','Living Room',4.5,3.8,2.7,'m'],['kitchen','Kitchen',2.6,2.2,2.7,'m']]],
    ['title'=>'Chamber & Hall at Takoradi Market Circle','description'=>'Affordable chamber and hall near Takoradi Market Circle. Easy access to market and buses.','type_id'=>3,'region_id'=>3,'city_id'=>8,'rent'=>380,'deposit'=>380,'bedrooms'=>1,'bathrooms'=>0,'toilets'=>0,'kitchens'=>0,'self'=>0,'parking'=>0,'water'=>1,'electric'=>'shared_meter','furnished'=>'unfurnished','level'=>'ground','walk'=>3,'vehicle'=>0,'road'=>'tarred','gated'=>0,'guard'=>0,'fenced'=>0,'community'=>'Market Circle','street'=>'John Sarbah Road','featured'=>0,'photos'=>10,'spaces'=>[['bedroom','Bedroom',3.2,2.8,2.6,'m'],['living_room','Hall',4.0,3.2,2.6,'m']]],
    ['title'=>'2-Bedroom House at Takoradi Anaji','description'=>'Well-built 2-bedroom house in Anaji estate. Large compound, borehole water and parking. Peaceful environment.','type_id'=>5,'region_id'=>3,'city_id'=>8,'rent'=>1400,'deposit'=>1400,'bedrooms'=>2,'bathrooms'=>1,'toilets'=>2,'kitchens'=>1,'self'=>1,'parking'=>1,'water'=>1,'electric'=>'prepaid','furnished'=>'unfurnished','level'=>'ground','walk'=>10,'vehicle'=>1,'road'=>'gravel','gated'=>1,'guard'=>0,'fenced'=>1,'community'=>'Anaji Estate','street'=>'Anaji Road','featured'=>0,'photos'=>11,'spaces'=>[['bedroom','Master Bedroom',4.0,3.5,2.8,'m'],['bedroom','Bedroom 2',3.5,3.0,2.8,'m'],['living_room','Living Room',5.2,4.0,2.8,'m']]],
    ['title'=>'Luxury 3-Bedroom at Accra Airport Residential','description'=>'Fully furnished luxury 3-bedroom apartment in Airport Residential Area. 24/7 security and parking.','type_id'=>7,'region_id'=>1,'city_id'=>1,'rent'=>8500,'deposit'=>8500,'bedrooms'=>3,'bathrooms'=>3,'toilets'=>3,'kitchens'=>1,'self'=>1,'parking'=>1,'water'=>1,'electric'=>'prepaid','furnished'=>'furnished','level'=>'second','walk'=>2,'vehicle'=>1,'road'=>'tarred','gated'=>1,'guard'=>1,'fenced'=>1,'community'=>'Airport Residential Area','street'=>'Independence Avenue','featured'=>1,'photos'=>12,'spaces'=>[['bedroom','Master Suite',6.0,5.0,3.2,'m'],['bedroom','Bedroom 2',4.8,4.0,3.2,'m'],['bedroom','Bedroom 3',4.5,3.8,3.2,'m'],['living_room','Open Plan Living',8.0,6.0,3.2,'m'],['kitchen','Kitchen',4.0,3.5,3.2,'m']]],
    ['title'=>'Single Room Self-Contained at Accra Ablekuma','description'=>'Brand new single room self-contained at Ablekuma. Tile floors, POP ceiling, own bathroom and toilet.','type_id'=>2,'region_id'=>1,'city_id'=>1,'rent'=>320,'deposit'=>320,'bedrooms'=>1,'bathrooms'=>1,'toilets'=>1,'kitchens'=>1,'self'=>1,'parking'=>0,'water'=>1,'electric'=>'prepaid','furnished'=>'unfurnished','level'=>'ground','walk'=>6,'vehicle'=>1,'road'=>'gravel','gated'=>1,'guard'=>0,'fenced'=>1,'community'=>'Ablekuma','street'=>'Ablekuma Road','featured'=>0,'photos'=>13,'spaces'=>[['bedroom','Room',3.4,2.9,2.7,'m'],['bathroom','Bathroom',1.8,1.5,2.7,'m']]],
    ['title'=>'2-Bedroom Self-Contained at Accra Dome','description'=>'Spacious 2-bedroom self-contained house at Dome. Large living area, 24/7 borehole water, parking.','type_id'=>6,'region_id'=>1,'city_id'=>1,'rent'=>1500,'deposit'=>1500,'bedrooms'=>2,'bathrooms'=>1,'toilets'=>1,'kitchens'=>1,'self'=>1,'parking'=>1,'water'=>1,'electric'=>'prepaid','furnished'=>'unfurnished','level'=>'ground','walk'=>5,'vehicle'=>1,'road'=>'tarred','gated'=>1,'guard'=>0,'fenced'=>1,'community'=>'Dome','street'=>'Dome Kwabenya Road','featured'=>0,'photos'=>14,'spaces'=>[['bedroom','Master Bedroom',4.0,3.5,2.8,'m'],['bedroom','Bedroom 2',3.5,3.0,2.8,'m'],['living_room','Living Room',5.5,4.2,2.8,'m'],['kitchen','Kitchen',3.0,2.5,2.8,'m']]],
];

// ── Force mode: remove previous sample properties ────────────
if ($force) {
    $oldIds = Database::fetchAll("SELECT id FROM properties WHERE title LIKE '% at %' AND title REGEXP '^(Single|Chamber|Studio|[0-9])' ");
    foreach ($oldIds as $row) {
        $pid = (int) $row['id'];
        foreach (Database::fetchAll("SELECT path, thumbnail FROM property_images WHERE property_id = ?", [$pid]) as $img) {
            foreach ([$img['path'], $img['thumbnail']] as $p) {
                if ($p && !preg_match('#^https?://#i', $p)) {
                    $abs = ROOT_PATH . '/public/' . ltrim($p, '/');
                    if (is_file($abs)) @unlink($abs);
                }
            }
        }
        Database::query("DELETE FROM property_images WHERE property_id = ?", [$pid]);
        Database::query("DELETE FROM property_locations WHERE property_id = ?", [$pid]);
        Database::query("DELETE FROM property_accessibility WHERE property_id = ?", [$pid]);
        Database::query("DELETE FROM property_security WHERE property_id = ?", [$pid]);
        Database::query("DELETE FROM property_spaces WHERE property_id = ?", [$pid]);
        Database::query("DELETE FROM properties WHERE id = ?", [$pid]);
    }
}

/**
 * Fetch a remote file over HTTPS.
 * Uses streams when allow_url_fopen is on, otherwise falls back to cURL
 * (shared hosts sometimes disable one or the other).
 */
function fetchRemote(string $url): ?string
{
    if (filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)) {
        $raw = @file_get_contents($url, false, stream_context_create([
            'http' => ['timeout' => 25, 'header' => "User-Agent: Mozilla/5.0\r\n"],
            'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
        ]));
        if ($raw !== false && strlen($raw) >= 2048) return $raw;
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_USERAGENT      => 'Mozilla/5.0',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $raw  = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if (is_string($raw) && $code === 200 && strlen($raw) >= 2048) return $raw;
    }

    return null;
}

/**
 * Download a remote image into public/uploads/properties/images/.
 * Returns ['path'=>..., 'thumbnail'=>...] with LOCAL 'uploads/...' paths,
 * or null when the download fails (caller skips the image).
 */
function downloadImage(string $url, string $dirAbs, int $idx): ?array
{
    $raw = fetchRemote($url);
    if ($raw === null) return null;

    $info = @getimagesizefromstring($raw);
    if (!$info) return null;
    [$w, $h, $type] = $info;

    $ext = match ($type) {
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_WEBP => 'webp',
        IMAGETYPE_GIF  => 'gif',
        default        => null,
    };
    if ($ext === null) return null;

    $filename = 'sample_' . date('Ymd') . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    if (@file_put_contents($dirAbs . '/' . $filename, $raw) === false) return null;

    // Thumbnail via GD (falls back to using the full image as thumb)
    $thumbName = null;
    if (function_exists('imagecreatetruecolor')) {
        $src = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromstring($raw),
            IMAGETYPE_PNG  => @imagecreatefromstring($raw),
            IMAGETYPE_WEBP => @imagecreatefromstring($raw),
            IMAGETYPE_GIF  => @imagecreatefromstring($raw),
            default        => null,
        };
        if ($src) {
            $ratio  = min(400 / $w, 300 / $h);
            $newW   = (int) round($w * $ratio);
            $newH   = (int) round($h * $ratio);
            $thumb  = imagecreatetruecolor($newW, $newH);
            if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
            }
            imagecopyresampled($thumb, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
            $thumbName = 'thumb_' . $filename;
            $ok = match ($type) {
                IMAGETYPE_JPEG => imagejpeg($thumb, $dirAbs . '/' . $thumbName, 85),
                IMAGETYPE_PNG  => imagepng($thumb, $dirAbs . '/' . $thumbName, 7),
                IMAGETYPE_WEBP => imagewebp($thumb, $dirAbs . '/' . $thumbName, 85),
                IMAGETYPE_GIF  => imagegif($thumb, $dirAbs . '/' . $thumbName),
                default        => false,
            };
            imagedestroy($src);
            imagedestroy($thumb);
            if (!$ok) { $thumbName = null; }
        }
    }

    $rel = 'uploads/properties/images/' . $filename;
    return [
        'path'      => $rel,
        'thumbnail' => $thumbName ? 'uploads/properties/images/' . $thumbName : $rel,
        'size'      => strlen($raw),
        'width'     => $w,
        'height'    => $h,
    ];
}

$inserted = 0;
$skipped  = 0;
$errors   = [];

foreach ($samples as $s) {
    // Idempotency: skip titles that already exist
    $exists = Database::fetchColumn(
        "SELECT id FROM properties WHERE title = ? AND deleted_at IS NULL LIMIT 1",
        [$s['title']]
    );
    if ($exists && !$force) {
        $skipped++;
        continue;
    }

    try {
        Database::beginTransaction();

        $propId = PropertyModel::create($landlordId, [
            'title'             => $s['title'],
            'description'       => $s['description'],
            'property_type_id'  => $s['type_id'],
            'rent_monthly'      => $s['rent'],
            'rent_annual'       => $s['rent'] * 12,
            'security_deposit'  => $s['deposit'],
            'negotiable'        => 0,
            'available_from'    => date('Y-m-d'),
            'num_bedrooms'      => $s['bedrooms'],
            'num_bathrooms'     => $s['bathrooms'],
            'num_toilets'       => $s['toilets'],
            'num_kitchens'      => $s['kitchens'],
            'furnished'         => $s['furnished'],
            'is_self_contained' => $s['self'],
            'building_level'    => $s['level'],
            'water_available'   => $s['water'],
            'electricity_type'  => $s['electric'],
            'has_parking'       => $s['parking'],
            'pet_policy'        => 'not_allowed',
        ]);

        PropertyModel::saveLocation($propId, ['region_id'=>$s['region_id'],'city_id'=>$s['city_id'],'community'=>$s['community'],'street'=>$s['street']]);
        PropertyModel::saveAccessibility($propId, ['walking_time_minutes'=>$s['walk'],'vehicle_accessible'=>$s['vehicle'],'car_accessible'=>$s['vehicle'],'road_condition'=>$s['road'],'taxi_accessible'=>1,'motorbike_accessible'=>1]);
        PropertyModel::saveSecurity($propId, ['gated'=>$s['gated'],'security_guard'=>$s['guard'],'fenced'=>$s['fenced'],'cctv'=>0,'street_lighting'=>1]);

        $spaceDefs = [];
        foreach ($s['spaces'] as $sp) {
            $spaceDefs[] = ['space_type'=>$sp[0],'space_name'=>$sp[1],'length'=>$sp[2],'width'=>$sp[3],'height'=>$sp[4],'unit'=>$sp[5]];
        }
        PropertyModel::saveSpaces($propId, $spaceDefs);

        foreach ($photoSets[$s['photos']] as $i => $url) {
            $local = downloadImage($url, $imgDirAbs, $i);
            if ($local === null) {
                $errors[] = "Image download failed for \"{$s['title']}\" (photo $i) — skipping that photo.";
                continue;
            }
            Database::insert('property_images', [
                'property_id' => $propId,
                'filename'    => basename($local['path']),
                'path'        => $local['path'],
                'thumbnail'   => $local['thumbnail'],
                'category'    => $cats[$i] ?? 'other',
                'caption'     => null,
                'sort_order'  => $i,
                'is_primary'  => ($i === 0) ? 1 : 0,
                'file_size'   => $local['size'],
                'width'       => $local['width'],
                'height'      => $local['height'],
                'uploaded_by' => $adminId,
            ]);
        }

        PropertyModel::updateStatus($propId, 'verified', $adminId, 'Sample property');
        if ($s['featured']) {
            Database::update('properties', ['is_featured'=>1,'featured_until'=>date('Y-m-d', strtotime('+1 year'))], 'id = ?', [$propId]);
        }

        Database::commit();
        $inserted++;
    } catch (\Throwable $e) {
        Database::rollBack();
        $errors[] = 'Failed "' . $s['title'] . '": ' . $e->getMessage();
    }
}

$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
?><!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sample Loader</title>
<style>body{font-family:system-ui,sans-serif;max-width:680px;margin:60px auto;padding:0 24px;color:#1a1a1a}.ok{background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:20px 24px;margin:20px 0}.err{background:#fef2f2;border:1.5px solid #fca5a5;border-radius:10px;padding:20px 24px;margin:20px 0}.warn{background:#fffbeb;border:1.5px solid #fde68a;border-radius:10px;padding:20px 24px;margin:20px 0}h1{font-size:22px;font-weight:800;margin-bottom:8px}h2{margin:0 0 8px;font-size:16px}ul{margin:8px 0 0 18px;font-size:13px}.cta{display:inline-block;margin-top:20px;padding:10px 22px;background:#235347;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;margin-right:10px}.del{margin-top:20px;padding:14px 18px;background:#fff8f0;border:1.5px solid #fde68a;border-radius:8px;font-size:13px}</style>
</head>
<body>
<h1>Sample Properties Loader</h1>
<?php if ($inserted > 0): ?>
<div class="ok"><h2>&#10003; <?= $inserted ?>/<?= count($samples) ?> properties inserted</h2><p style="font-size:13px;color:#166534;margin:4px 0 0">Images downloaded and self-hosted under <code>public/uploads/properties/images/</code> — they will remain live permanently.</p></div>
<?php endif; ?>
<?php if ($skipped > 0): ?>
<div class="warn"><h2><?= $skipped ?> already existed (skipped)</h2><p style="font-size:13px;color:#92400e;margin:4px 0 0">Re-run with <code>?force=1</code> to remove previous samples and start clean.</p></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
<div class="err"><h2>&#9888; <?= count($errors) ?> notice(s)</h2><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<a href="<?= $appUrl ?>/properties" class="cta">View properties &rarr;</a>
<a href="<?= $appUrl ?>/admin/dashboard" class="cta" style="background:#163832">Admin dashboard &rarr;</a>
<div class="del"><strong>&#9888; Delete this file now</strong> — remove <code>public/add_samples.php</code> from the server.</div>
</body></html>
