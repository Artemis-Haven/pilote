<?php

namespace Pilote\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PiloteUserBundle extends Bundle
{
    public function getParent() {
        return 'FOSUserBundle';
    }
}
