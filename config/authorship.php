<?php



use App\Support\Authorship;

return [

    'author' => Authorship::AUTHOR,

    'email' => Authorship::EMAIL,

    'github' => Authorship::LINK,

    'copyright' => '© '.Authorship::YEAR.' '.Authorship::AUTHOR.'. All rights reserved.',

    'project' => Authorship::PROJECT,

    /*
     * Build fingerprint: the SHA-256 of the canonical identity string, which
     * anyone can recompute without access to this repository. See ATTRIBUTION.md
     * for the string it is taken over and the one-line command to verify it.
     */
    'fingerprint' => Authorship::FINGERPRINT,

];
