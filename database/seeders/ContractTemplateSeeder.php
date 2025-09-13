<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ContractTemplate;

class ContractTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Standard Employment Contract',
                'key' => 'standard_employment',
                'description' => 'Standard employment contract template for regular employees',
                'content' => '
                    <h3>Employment Agreement</h3>
                    <p>This Employment Agreement ("Agreement") is entered into between <strong>{{employee_name}}</strong> ("Employee") and Pizzaburg ("Company") on <strong>{{generated_date}}</strong>.</p>
                    
                    <h3>Position and Responsibilities</h3>
                    <p>The Employee will serve as <strong>{{position_name}}</strong> (Grade: {{position_grade}}) at our <strong>{{branch_name}}</strong> location.</p>
                    <p><strong>Branch Address:</strong> {{branch_address}}</p>
                    
                    <h3>Employment Terms</h3>
                    <ul>
                        <li><strong>Start Date:</strong> {{start_date}}</li>
                        <li><strong>Salary:</strong> {{salary}}</li>
                        <li><strong>Contact Information:</strong> {{employee_email}}, {{employee_phone}}</li>
                    </ul>
                    
                    <h3>Terms and Conditions</h3>
                    <p>The Employee agrees to:</p>
                    <ul>
                        <li>Perform duties as assigned by the Company</li>
                        <li>Maintain confidentiality of company information</li>
                        <li>Follow all company policies and procedures</li>
                        <li>Provide notice as required by labor law for termination</li>
                    </ul>
                    
                    <h3>Company Obligations</h3>
                    <p>The Company agrees to:</p>
                    <ul>
                        <li>Provide a safe working environment</li>
                        <li>Pay agreed salary on time</li>
                        <li>Provide necessary training and support</li>
                        <li>Comply with all applicable labor laws</li>
                    </ul>
                ',
                'is_active' => true,
                'version' => '1.0',
            ],
            [
                'name' => 'Manager Employment Contract',
                'key' => 'manager_employment',
                'description' => 'Employment contract template for management positions',
                'content' => '
                    <h3>Management Employment Agreement</h3>
                    <p>This Management Employment Agreement ("Agreement") is entered into between <strong>{{employee_name}}</strong> ("Manager") and Pizzaburg ("Company") on <strong>{{generated_date}}</strong>.</p>
                    
                    <h3>Management Position</h3>
                    <p>The Manager will serve as <strong>{{position_name}}</strong> (Grade: {{position_grade}}) at our <strong>{{branch_name}}</strong> location.</p>
                    <p><strong>Branch Address:</strong> {{branch_address}}</p>
                    
                    <h3>Employment Terms</h3>
                    <ul>
                        <li><strong>Start Date:</strong> {{start_date}}</li>
                        <li><strong>Salary:</strong> {{salary}}</li>
                        <li><strong>Contact Information:</strong> {{employee_email}}, {{employee_phone}}</li>
                    </ul>
                    
                    <h3>Management Responsibilities</h3>
                    <p>In addition to standard employment terms, the Manager agrees to:</p>
                    <ul>
                        <li>Lead and supervise team members</li>
                        <li>Ensure operational excellence</li>
                        <li>Maintain high customer service standards</li>
                        <li>Report to senior management regularly</li>
                        <li>Handle disciplinary matters as needed</li>
                    </ul>
                    
                    <h3>Additional Benefits</h3>
                    <ul>
                        <li>Performance bonus eligibility</li>
                        <li>Additional vacation days</li>
                        <li>Professional development opportunities</li>
                    </ul>
                ',
                'is_active' => true,
                'version' => '1.0',
            ],
            [
                'name' => 'Part-time Employment Contract',
                'key' => 'parttime_employment',
                'description' => 'Employment contract template for part-time employees',
                'content' => '
                    <h3>Part-time Employment Agreement</h3>
                    <p>This Part-time Employment Agreement ("Agreement") is entered into between <strong>{{employee_name}}</strong> ("Employee") and Pizzaburg ("Company") on <strong>{{generated_date}}</strong>.</p>
                    
                    <h3>Part-time Position</h3>
                    <p>The Employee will serve as <strong>{{position_name}}</strong> (Grade: {{position_grade}}) on a part-time basis at our <strong>{{branch_name}}</strong> location.</p>
                    <p><strong>Branch Address:</strong> {{branch_address}}</p>
                    
                    <h3>Employment Terms</h3>
                    <ul>
                        <li><strong>Start Date:</strong> {{start_date}}</li>
                        <li><strong>Salary:</strong> {{salary}} (pro-rated for part-time hours)</li>
                        <li><strong>Contact Information:</strong> {{employee_email}}, {{employee_phone}}</li>
                    </ul>
                    
                    <h3>Part-time Specific Terms</h3>
                    <ul>
                        <li>Flexible scheduling based on business needs</li>
                        <li>Pro-rated benefits as per company policy</li>
                        <li>Opportunity for additional hours during peak periods</li>
                        <li>Standard employment protections apply</li>
                    </ul>
                ',
                'is_active' => true,
                'version' => '1.0',
            ],
        ];

        foreach ($templates as $template) {
            ContractTemplate::create($template);
        }
    }
}
