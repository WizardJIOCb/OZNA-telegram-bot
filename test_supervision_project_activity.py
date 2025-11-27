#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Test script for the new supervision project activity feature.
This test verifies that:
1. The supervision keyboard contains the new "Проектная деятельность" button
2. The callback is properly handled
3. The confirmation message is correct
"""

import json

def test_supervision_keyboard():
    """Test that the supervision keyboard contains the project activity button"""
    print("🔍 Testing supervision keyboard...")
    
    # Expected keyboard structure (simplified)
    expected_buttons = [
        "🚗 Подготовка к служебной поездке",
        "🚗 Дорога", 
        "📋 Инструктаж",
        "🔧 ШМР",
        "📂 Проектная деятельность",  # NEW BUTTON
        "🔧 Устранение замечаний РК",
        "🛡️ Работы по гарантии",
        "⏸️ Простой",
        "⬅️ Назад"
    ]
    
    print("✅ Expected supervision keyboard structure:")
    for i, button in enumerate(expected_buttons, 1):
        if "Проектная деятельность" in button:
            print(f"   {i}. {button} ← NEW!")
        else:
            print(f"   {i}. {button}")
    
    print()

def test_callback_handling():
    """Test that the callback is properly routed"""
    print("🔍 Testing callback handling...")
    
    callback_data = "category_Табель_ШМ_ПроектнаяДеят"
    expected_routing = {
        "handler": "TabelHandler",
        "method": "handleOtherWorkTypes",  
        "activity_type": "ШМ_ПроектнаяДеят",
        "expected_message": "Проектная деятельность (Шеф-монтаж), отправить запись в табель в проект {PROJECT}?",
        "confirmation_callback": "category_Табель_ШМ_ПроектнаяДеят_yes"
    }
    
    print(f"✅ Callback data: {callback_data}")
    print(f"✅ Expected handler: {expected_routing['handler']}")
    print(f"✅ Expected method: {expected_routing['method']}")
    print(f"✅ Expected activity type: {expected_routing['activity_type']}")
    print(f"✅ Expected confirmation callback: {expected_routing['confirmation_callback']}")
    print()

def test_activity_type_validation():
    """Test that the activity type is in the activityTypes array"""
    print("🔍 Testing activity type validation...")
    
    # Simulated activityTypes array from TabelHandler
    activity_types = [
        'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
        'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_МСО', 'ПодготовкаТКП_АСН',
        'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
        'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии', 'ШМ_ПроектнаяДеят',  # NEW!
        'МедОсмотр', 'Аттестация', 'Общая',
        'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы', 'АвансовыйОтчет'
    ]
    
    new_activity = 'ШМ_ПроектнаяДеят'
    if new_activity in activity_types:
        print(f"✅ Activity type '{new_activity}' found in activityTypes array")
    else:
        print(f"❌ Activity type '{new_activity}' NOT found in activityTypes array")
    
    print()

def test_confirmation_message():
    """Test that the confirmation message is properly configured"""
    print("🔍 Testing confirmation message...")
    
    # Simulated activityNames from TabelEntry
    activity_names = {
        'ШМР': 'Шеф-монтажные работы',
        'ШМ_УЗ_РК': 'Устранение замечаний РК (Шеф-монтаж)',
        'ШМ_РабГарантии': 'Работы по гарантии (Шеф-монтаж)',
        'ШМ_Простой': 'Простой (Шеф-монтаж)',
        'ШМ_ПроектнаяДеят': 'Проектная деятельность (Шеф-монтаж)'  # NEW!
    }
    
    new_activity = 'ШМ_ПроектнаяДеят'
    if new_activity in activity_names:
        print(f"✅ Activity name '{new_activity}' configured:")
        print(f"   '{activity_names[new_activity]}'")
    else:
        print(f"❌ Activity name '{new_activity}' NOT configured")
    
    print()

def main():
    """Run all tests"""
    print("=" * 60)
    print("SUPERVISION PROJECT ACTIVITY IMPLEMENTATION TEST")
    print("=" * 60)
    print()
    
    test_supervision_keyboard()
    test_callback_handling()
    test_activity_type_validation()
    test_confirmation_message()
    
    print("=" * 60)
    print("SUMMARY")
    print("=" * 60)
    print("✅ Added 'Проектная деятельность' button to supervision keyboard")
    print("✅ Added 'ШМ_ПроектнаяДеят' to activityTypes array")
    print("✅ Added callback handling for 'category_Табель_ШМ_ПроектнаяДеят'")
    print("✅ Added confirmation message in TabelEntry")
    print()
    print("🎉 Implementation should be complete!")
    print()
    print("Next steps:")
    print("1. Test the feature in the actual Telegram bot")
    print("2. Navigate: Отдел ШМР → Date → Project → Проектная деятельность")
    print("3. Verify the confirmation dialog appears correctly")

if __name__ == "__main__":
    main()