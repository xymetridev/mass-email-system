<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;

class AutomationController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        $automations = $db->table('automations')
            ->select('automations.*, tags.name as trigger_tag_name')
            ->join('tags', 'tags.id = automations.trigger_tag_id', 'left')
            ->where('automations.user_id', auth()->id())
            ->get()->getResultArray();

        $data = [
            'pageTitle'   => 'Automations (Email Sequences)',
            'automations' => $automations,
            'tags'        => $db->table('tags')->where('user_id', auth()->id())->get()->getResultArray(),
            'templates'   => $db->table('templates')->where('user_id', auth()->id())->get()->getResultArray()
        ];

        return view('user/automations/index', $data);
    }

    public function store()
    {
        $db = \Config\Database::connect();
        
        $name = $this->request->getPost('name');
        $tagId = $this->request->getPost('trigger_tag_id');

        if ($name && $tagId) {
            $db->table('automations')->insert([
                'user_id' => auth()->id(),
                'name'    => $name,
                'trigger_tag_id' => $tagId,
                'status'  => 'PAUSED',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        return redirect()->back()->with('success', 'Automation Flow berhasil dibuat.');
    }

    public function show($id)
    {
        $db = \Config\Database::connect();
        
        $automation = $db->table('automations')
            ->select('automations.*, tags.name as trigger_tag_name')
            ->join('tags', 'tags.id = automations.trigger_tag_id', 'left')
            ->where(['automations.id' => $id, 'automations.user_id' => auth()->id()])
            ->get()->getRowArray();
        if (!$automation) return redirect()->to(url_to('user.automations'));

        $steps = $db->table('automation_steps')
            ->select('automation_steps.*, templates.name as template_name')
            ->join('templates', 'templates.id = automation_steps.template_id')
            ->where('automation_id', $id)
            ->orderBy('step_order', 'ASC')
            ->get()->getResultArray();

        $data = [
            'pageTitle'  => 'Desain Flow: ' . $automation['name'],
            'automation' => $automation,
            'steps'      => $steps,
            'templates'  => $db->table('templates')->where('user_id', auth()->id())->get()->getResultArray()
        ];

        return view('user/automations/show', $data);
    }

    public function storeStep($id)
    {
        $db = \Config\Database::connect();
        
        $templateId = $this->request->getPost('template_id');
        $delayDays  = $this->request->getPost('delay_days');

        // Cari urutan terakhir
        $lastOrder = $db->table('automation_steps')
            ->where('automation_id', $id)
            ->orderBy('step_order', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        $nextOrder = $lastOrder ? $lastOrder['step_order'] + 1 : 1;

        $db->table('automation_steps')->insert([
            'automation_id' => $id,
            'step_order'    => $nextOrder,
            'template_id'   => $templateId,
            'delay_days'    => $delayDays
        ]);

        return redirect()->back()->with('success', 'Langkah berhasil ditambahkan ke flow.');
    }

    public function updateStatus($id, $status)
    {
        $db = \Config\Database::connect();
        $db->table('automations')->where(['id' => $id, 'user_id' => auth()->id()])->update(['status' => $status]);
        
        return redirect()->back()->with('success', 'Status automation diperbarui.');
    }
}
