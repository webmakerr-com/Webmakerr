<?php

namespace Webmakerr\App\Hooks\Handlers\BlockEditors\Buttons;

use Webmakerr\App\Hooks\Handlers\BlockEditors\BlockEditor;
//use Webmakerr\App\Hooks\Handlers\ShortCodes\Buttons\AddToCartShortcode;
//use Webmakerr\App\Hooks\Handlers\ShortCodes\Buttons\DirectCheckoutShortcode;
use Webmakerr\App\Services\Translations\TransStrings;
use Webmakerr\Framework\Support\Arr;

class AddToCartButtonEditor extends BlockEditor
{
    protected static string $editorName = 'add-to-cart-button';



    protected function getStyles(): array
    {
        return ['admin/BlockEditor/Buttons/AddToCart/style/style.css'];
    }


    protected function localizeData(): array
    {
        return [];
    }

    public function render(array $shortCodeAttribute, $block = null): string
    {
        return 'Hello';
    }

}
