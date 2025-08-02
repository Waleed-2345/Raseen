<?php
session_start();

// *** ملاحظة أمنية: في الإنتاج يفضل عدم وضع المفتاح صريحاً هنا، بل من متغير بيئة أو ملف .env غير مضمَّن في Git ***
$apiKey = "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";

if (isset($_POST['reset'])) {
    unset($_SESSION['chat_history']);
    echo "تم بدء محادثة جديدة.";
    exit;
}

if (isset($_POST['question'])) {
    if (empty($apiKey)) {
        echo "API key is not configured.";
        exit;
    }

    $prompt = trim($_POST['question']);

    $system_prompt = <<<'EOD'
في بداية المحادثة، ابدأ التحية وعرّف بنفسك كمساعد استثماري ذكي ضمن منصة رصين. 
يجب أن تكون ملمًا بكافة أنواع وأماكن الاستثمار في المملكة العربية السعودية، وتوزيعها بحسب المناطق والمدن الرئيسية، كالتالي:
- المنطقة الوسطى (مثل: الرياض، القصيم): الاستثمارات الأكثر شيوعًا هي التجارية، الإدارية، والعقارية.
- المنطقة الشرقية (مثل: الدمام، الخبر): تبرز فيها الاستثمارات الصناعية والعقارية.
- المنطقة الغربية (مثل: جدة، ينبع): تشتهر بالاستثمارات اللوجستية والعقارية.
- المنطقة الجنوبية (مثل: أبها، عسير، الباحة): تبرز فيها الاستثمارات السياحية والعقارية.
عند استفسار المستخدم عن الاستثمار، اسأله أولاً عن المجال الذي يهتم به (تجاري، صناعي، لوجستي، سياحي، عقاري... إلخ)، 
وإذا لم يكن متأكدًا أو محتارًا بين عدة مجالات أو مناطق، اسأله عن أهدافه واهتماماته الأساسية (مثل: العائد المالي، النمو المستقبلي، الأمان، نوع السوق...). 
بناءً على ذلك، قدّم له مقارنة واضحة ومختصرة بين الخيارات، موضحًا مزايا كل منطقة أو مجال بحسب اهتماماته، وساعده على اتخاذ قرار مناسب.
إذا سُئلت عن مدينة أو محافظة غير مذكورة لديك صراحة، حاول تحديد المنطقة الإدارية التابعة لها قدر الإمكان، ثم قدّم النصيحة حسب نوع الاستثمار المناسب للمنطقة. 
إذا لم تتوفر لديك معلومات كافية عن المدينة، اعتذر للمستخدم واقترح عليه مراجعة الفرص في المناطق الرئيسية.
عند طلب مقارنة بين منطقتين أو مجالين أو إذا كان المستخدم محتارًا بين عدة خيارات،
وضّح الفروق الرئيسية بين كل خيار بنقاط مرتبة أو جدول مختصر، وساعده على اختيار الأنسب بحسب اهتماماته وظروفه الاستثمارية.
استخدم دائمًا اللغة العربية الفصحى وأسلوبًا مهنيًا واضحًا ومنظمًا، وابتعد عن التكرار والإطالة غير الضرورية، 
واكتفِ بالترحيب مرة واحدة فقط في بداية الحوار.
EOD;

    if (!isset($_SESSION['chat_history'])) {
        $_SESSION['chat_history'] = [
            ["role" => "system", "content" => $system_prompt]
        ];
    }
    $_SESSION['chat_history'][] = ["role" => "user", "content" => $prompt];

    $data = [
        "model" => "gpt-4",
        "messages" => $_SESSION['chat_history'],
        "max_tokens" => 1000,
    ];

    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $apiKey,
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        $curlErr = curl_error($ch);
        curl_close($ch);
        echo "cURL error: " . $curlErr;
        exit;
    }
    curl_close($ch);

    $response = json_decode($result, true);

    if (isset($response["choices"][0]["message"]["content"])) {
        $assistant_reply = $response["choices"][0]["message"]["content"];
        $_SESSION['chat_history'][] = ["role" => "assistant", "content" => $assistant_reply];
        echo $assistant_reply;
    } else {
        $errMsg = $response["error"]["message"] ?? "حدث خطأ تقني أثناء الاتصال بـ OpenAI.";
        echo $errMsg;
    }
    exit;
}
?>
