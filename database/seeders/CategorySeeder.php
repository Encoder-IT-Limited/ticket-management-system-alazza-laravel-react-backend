<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Hardware',
                'children' => [
                    ['name' => 'Computer / Laptop Issues'],
                    ['name' => 'Monitor / Display'],
                    ['name' => 'Keyboard / Mouse / Peripherals'],
                    ['name' => 'Printer / Scanner'],
                    ['name' => 'Network Equipment'],
                    ['name' => 'Hardware Replacement / Spare Parts'],
                ],
            ],
            [
                'name' => 'Software',
                'children' => [
                    ['name' => 'Operating System Issues'],
                    ['name' => 'Software Installation / Update'],
                    ['name' => 'Application Issues'],
                    ['name' => 'License & Activation'],
                    ['name' => 'Antivirus / Security Software'],
                ],
            ],
            [
                'name' => 'Network & Connectivity',
                'children' => [
                    ['name' => 'Internet / Wi-Fi Issues'],
                    ['name' => 'VPN Access'],
                    ['name' => 'Email / Outlook Issues'],
                    ['name' => 'Server Access / Remote Desktop'],
                    ['name' => 'IP Address / DNS Issues'],
                ],
            ],
            [
                'name' => 'User Accounts & Security',
                'children' => [
                    ['name' => 'Password Reset / Account Unlock'],
                    ['name' => 'Access Permissions / Authorization'],
                    ['name' => 'Phishing / Suspicious Emails'],
                    ['name' => 'Multi-Factor Authentication (MFA)'],
                ],
            ],
            [
                'name' => 'IT Services & Requests',
                'children' => [
                    ['name' => 'New User Setup'],
                    ['name' => 'IT Equipment Request'],
                    ['name' => 'Backup & Data Recovery'],
                    ['name' => 'Software Procurement'],
                    ['name' => 'Policy & Compliance Requests'],
                ],
            ],
            [
                'name' => 'Other',
                'children' => [
                    ['name' => 'General IT Inquiry'],
                    ['name' => 'Training & Documentation'],
                    ['name' => 'IT Consultation'],
                ],
            ],
        ];


        foreach ($categories as $category) {
            $parentCategory = \App\Models\Category::updateOrCreate([
                'name' => $category['name'],
                'description' => $category['name'] . ' Category',
            ]);

            foreach ($category['children'] as $child) {
                \App\Models\Category::updateOrCreate([
                    'name' => $child['name'],
                    'description' => $child['name'] . ' Category',
                    'parent_id' => $parentCategory->id,
                ]);
            }
        }
    }
}
