                'ИзмСх' => 'ИзмСх_ОхрСигн',
                'МонтКб' => 'МонтКб_ОхрСигн',
                'РаскКб' => 'РаскКб_ОхрСигн',
                'МонтОб' => 'МонтОб_ОхрСигн',
                // Removed 'ДопРаб' mapping - it's a submenu, not direct activity execution
                'ПодтВех' => 'ПодтВех_ОхрСигн'
                // Removed 'Проч' mapping to allow custom input handling
            ];
            
            if (isset($shortToFullMap[$shortActivityKey])) {
                $activityType = $shortToFullMap[$shortActivityKey];
            }
        }
        // Handle short callback format for supervision works milestone confirmations
        elseif (preg_match('/^cat_ШМР_(.+)_ПодтВех_yes$/u', $category, $matches)) {
            error_log("TabelHandler: handleActivityExecution - Processing short supervision milestone callback: {$category}");
            // Map short format to full activity type
            $shortToFullMap = [
                'ВхКонтр' => 'ШМР_ВходнойКонтроль_ПодтВех',
                'ЗавОбор' => 'ШМР_ЗавозОборудования_ПодтВех',
                'АналФунд' => 'ШМР_АнализИнформацииПоФундаменту_ПодтВех',
                'РасстБлок' => 'ШМР_РасстановкаБлоков_ПодтВех',
                'МонтПлощ' => 'ШМР_МонтажПлощадокОбслуживания_ПодтВех',
                'МонтЭлУкр' => 'ШМР_МонтажЭлементовУкрытия_ПодтВех',
                'СборКровл' => 'ШМР_СборкаКровли_ПодтВех',
                'МонтТруб' => 'ШМР_МонтажТрубопроводов_ПодтВех',
                'ГидрИсп' => 'ШМР_ГидропневмоИспытания_ПодтВех',
                'МонтТеплИз' => 'ШМР_МонтажТеплоизоляции_ПодтВех',
                'МонтВент' => 'ШМР_МонтажВентиляции_ПодтВех',
                'МонтСнятОб' => 'ШМР_МонтажСнятогоОборудования_ПодтВех',
                'МонтКаб' => 'ШМР_МонтажКабельнойПродукции_ПодтВех'
            ];