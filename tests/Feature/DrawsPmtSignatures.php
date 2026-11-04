<?php

namespace Tests\Feature;

use App\Models\User;

trait DrawsPmtSignatures
{
    // A hand-drawn-looking stroke, unique per member, as the PNG data URL the signature pad sends.
    protected function signatureFor(User $member): string
    {
        $img = imagecreatetruecolor(300, 100);
        imagesavealpha($img, true);
        imagefill($img, 0, 0, imagecolorallocatealpha($img, 0, 0, 0, 127));
        $ink = imagecolorallocate($img, 17, 24, 39);
        imagesetthickness($img, 3);

        mt_srand($member->id);
        [$prevX, $prevY] = [15, 55];
        for ($x = 21; $x <= 285; $x += 6) {
            $y = 55 + (int) (25 * sin($x / (10 + $member->id % 7))) + mt_rand(-6, 6);
            imageline($img, $prevX, $prevY, $x, $y, $ink);
            [$prevX, $prevY] = [$x, $y];
        }
        imageline($img, 30, 88, 270, 84, $ink);

        ob_start();
        imagepng($img);
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode(ob_get_clean());
    }
}
