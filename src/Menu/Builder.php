<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Menu;

use Knp\Menu\ItemInterface;
use Nines\UtilBundle\Menu\AbstractBuilder;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class to build some menus for navigation.
 */
class Builder extends AbstractBuilder {
    use ContainerAwareTrait;

    /**
     * @param array<string,mixed> $options
     */
    public function dcMenu(array $options) : ItemInterface {
        $menu = $this->dropdown($options['title'] ?? 'Dublin Core');

        $menu->addChild('Elements', [
            'route' => 'element_index',
        ]);
        $menu->addChild('Values', [
            'route' => 'value_index',
        ]);

        if ($this->hasRole('ROLE_CONTENT_ADMIN')) {
            $this->addDivider($menu, 'divider_content');
            $menu->addChild('Content Admin', [
                'uri' => '#',
            ]);
        }

        if ($this->hasRole('ROLE_ADMIN')) {
            $this->addDivider($menu, 'divider_admin');
            $menu->addChild('Admin', [
                'uri' => '#',
            ]);
        }

        return $menu->getParent();
    }
}
