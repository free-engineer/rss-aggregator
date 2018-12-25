<?php

namespace Views\Template;
/*
 * Refferd to this article. (Japanese)
 * 
 * 60行テンプレートエンジンがパワーアップしてレイアウト機能に対応
 * 60lines template became a "Otokomae" (means handsome) Template
 * https://anond.hatelabo.jp/20071108232701
 * 
 */

class Otokomae
{

    public function embed($filename, $context, $layout=null)
    {
        $content = $this->renderTemplate($filename, $context);

        // If layout set on context side, use it.
        if (isset($context['layout'])) {
            $layout = $context['layout'];
        }
        if($layout) {
            $context['content'] = $content;
            $content = $this->renderTemplate($layout, $context);
        } else {
            $content = "Layout Error.";
        }
        echo $content;
    }

    public function renderTemplate($filename, $context)
    {
        $cache = $this->convertTemplate($filename);
        extract($context);
        ob_start();
        include $cache;
        return ob_get_clean();
    }

    public function convertTemplate($filename)
    {
        if (! file_exists($filename)) {
            
        }
        $cache = $filename . '.out';
        $s = file_get_contents( __DIR__ . '/' . $filename);
        $s = $this->convertString($s);
        file_put_contents($cache, $s, LOCK_EX);

        return $cache;
    }

    private function convertString($s)
    {
        $s = preg_replace('/^<\?xml/', '<<?php ?>?xml', $s);
        $s = preg_replace('/#\{(.*?)\}/', '<?php echo $1; ?>', $s);
        $s = preg_replace('/%\{(.*?)\}/', '<?php echo htmlspecialchars($1); ?>', $s);
        return $s;
    }
}
