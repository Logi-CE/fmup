<?php
namespace FMUP\Dispatcher\Plugin;


class AssetCompressor extends \FMUP\Dispatcher\Plugin
{
    private $versionComponent;

    /**
     * Can be used to apply something on request object
     */
    public function handle()
    {
        $response = $this->getResponse()->getBody();
        if (preg_match_all('~<link (.*)href="([^"]+)"(.*)/>~u', $response, $matches)) {
            $mediaCombined = array();
            foreach ($matches[0] as $key => $match) {
                if (
                    false !== strpos($match, 'rel="stylesheet"') &&
                    false !== strpos($match, 'type="text/css"')
                ) {
                    $mediaSelected = preg_match('~media="([^"]+)"~', $match, $media) ? $media[1] : 'screen';
                    foreach (explode(',', $mediaSelected) as $mediaSelectedSplit) {
                        $mediaCombined[trim($mediaSelectedSplit)][] = str_replace(
                            '/',
                            \FMUP\Controller\AssetCompressor::REPLACE_SEPARATOR,
                            ltrim($matches[2][$key], '/')
                        );
                    }
                    $response = str_replace($match, '', $response);
                }
            }
            $res = '';
            foreach ($mediaCombined as $mediaSelected => $hrefs) {
                $res .= '<link rel="stylesheet" media="' . $mediaSelected . '" href="/styles/cached/';
                $res .= $this->getVersionComponent()->get() . '/' . implode(',', str_replace('.css', '', $hrefs)) . '.css" />';
            }
            $this->getResponse()->setBody(str_replace('<head>', '<head>' . $res, $response));
        }
    }

    /**
     * @return \FMUP\ProjectVersion
     */
    public function getVersionComponent()
    {

        if (!$this->versionComponent) {
            $this->versionComponent = \FMUP\ProjectVersion::getInstance();
        }
        return $this->versionComponent;
    }

    /**
     * @param \FMUP\ProjectVersion $versionComponent
     * @return $this
     */
    public function setVersionComponent(\FMUP\ProjectVersion $versionComponent)
    {
        $this->versionComponent = $versionComponent;
        return $this;
    }
}
