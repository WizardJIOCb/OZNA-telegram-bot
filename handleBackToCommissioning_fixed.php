    /**
     * Handle back to commissioning menu
     */
    private function handleBackToCommissioning(string $chatId, Conversation $conversation): ServerResponse
    {
        $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
        
        // Check if we're coming from an equipment submenu by looking at recent conversation activity
        // When accessing equipment menus, specific callback patterns are used
        $currentCategory = $conversation->notes['current_category'] ?? '';
        
        // Equipment menu callbacks that lead to submenus
        $equipmentMenuCallbacks = [
            'Табель_Электрооборудование',
            'cat_ТабЭлОб',
            'Табель_Автоматизация',
            'cat_ТабАвтом',
            'Табель_ПожарнаяСигнализация',
            'cat_ТабПожСигн',
            'Табель_ОхраннаяСигнализация',
            'cat_ТабОхрСигн',
            'Табель_ОтоплениеИВентиляция',
            'cat_ТабВентОтопл',
            'Табель_ТехнологическоеОборудование',
            'cat_ТабТехОб',
            'Табель_Метрология',
            'cat_ТабМетрол'
        ];
        
        // Additional works callbacks that also lead to equipment submenus
        $additionalWorksCallbacks = [
            'cat_ТабЭлОб_ДопРаб',
            'cat_ТабАвтом_ДопРаб',
            'cat_ТабПожСигн_ДопРаб',
            'cat_ТабОхрСиг_ДопРаб',
            'cat_ТабВентОтопл_ДопРаб',
            'cat_ТабТехОб_ДопРаб',
            'cat_ТабМетрол_ДопРаб'
        ];
        
        $isFromEquipmentMenu = false;
        
        // Check if we're coming from an equipment menu or additional works menu
        if (in_array($currentCategory, $equipmentMenuCallbacks) || in_array($currentCategory, $additionalWorksCallbacks)) {
            $isFromEquipmentMenu = true;
        }
        
        // Also check if we have equipment-specific activity types in recent conversation
        if (!$isFromEquipmentMenu) {
            $equipmentActivityPatterns = [
                'РабПоГр_ЭлектрОб', 'ФормИД_ЭлектрОб', 'ПодтВех_ЭлектрОб',
                'РабПоГр_Автом', 'ФормИД_Автом', 'ПодтВех_Автом',
                'РабПоГр_ПожСигн', 'ФормИД_ПожСигн', 'ПодтВех_ПожСигн',
                'РабПоГр_ОхрСигн', 'ФормИД_ОхрСигн', 'ПодтВех_ОхрСигн',
                'РабПоГр_ВентОтопл', 'ФормИД_ВентОтопл', 'ПодтВех_ВентОтопл',
                'РабПоГр_ТехОб', 'ФормИД_ТехОб', 'ПодтВех_ТехОб',
                'РабПоГр_Метрол', 'ФормИД_Метрол', 'ПодтВех_Метрол'
            ];
            
            foreach ($equipmentActivityPatterns as $pattern) {
                if (isset($conversation->notes['current_category']) && 
                    strpos($conversation->notes['current_category'], $pattern) !== false) {
                    $isFromEquipmentMenu = true;
                    break;
                }
            }
        }
        
        if ($isFromEquipmentMenu) {
            // Coming from equipment submenu - go back to commissioning works list
            $keyboard = $this->messageService->createCommissioningWorksKeyboard();
            return $this->messageService->sendMessage(
                $chatId, 
                "<b>⚙️ Пусконаладочные работы для проекта {$projectCode}</b>\n\nВыберите тип работ:",
                $keyboard
            );
        } else {
            // Coming from commissioning works list - go back to main commissioning menu (Отдел ПНР)
            $keyboard = $this->messageService->createCommissioningKeyboard();
            return $this->messageService->sendMessage(
                $chatId, 
                "<b>⚙️ Отдел ПНР для проекта {$projectCode}</b>",
                $keyboard
            );
        }
    }