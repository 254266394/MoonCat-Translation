<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>翻译工具</title>
<style>
body {
font-family: Arial, sans-serif;
margin: 50px;
}
.container {
max-width: 600px;
margin: 0 auto;
}
h1 {
text-align: center;
}
form {
display: flex;
flex-direction: column;
}
label {
margin-bottom: 10px;
}
input, select, button {
margin-bottom: 20px;
padding: 10px;
font-size: 16px;
}
#result {
margin-top: 20px;
padding: 20px;
background-color: #f4f4f4;
border-radius: 5px;
}
</style>
</head>
<body>
<div class="container">
<h1>月半猫翻译工具</h1>
<form id="translationForm">
<label for="text">要翻译的文本:</label>
<input type="text" id="text" name="text" required>

<label for="target">目标语言:</label>
<select id="target" name="target" required>
<option value="zh">中文</option>
<option value="en">英文</option>
</select>
<button type="submit">翻译</button>
</form>
<div id="result"></div>
</div>
<script>
document.getElementById('translationForm').addEventListener('submit', function(event) {
event.preventDefault();

const text = document.getElementById('text').value;
const target = document.getElementById('target').value;

fetch(`/lego.php?action=translate&text=${encodeURIComponent(text)}&target=${target}`)
.then(response => response.json())
.then(data => {
const resultDiv = document.getElementById('result');
if (data.status === 'success') {
resultDiv.innerHTML = `<strong>翻译结果:</strong> ${data.translatedText}`;
} else {
resultDiv.innerHTML = `<strong>错误:</strong> ${data.message}`;
}
})
.catch(error => {
console.error('Error:', error);
});
});
</script>
</body>
</html>