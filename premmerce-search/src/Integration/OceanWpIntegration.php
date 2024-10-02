<?php namespace Premmerce\Search\Integration;

class OceanWpIntegration
{
    public function __construct()
    {
        add_filter('premmerce_search_localize_array', function ($localizeData) {
            if ($localizeData['searchField'] != '') {
                $localizeData['searchField'] = '.header-searchform input ,' . $localizeData['searchField'];
            } else {
                $localizeData['searchField'] .= '.header-searchform input, .header-searchform-wrap input';
            }

            return $localizeData;
        });
    }
}
