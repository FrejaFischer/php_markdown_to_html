document.querySelector('#frmMarkdown').addEventListener('submit', function (e) {
    e.preventDefault();
    
    const markdownCode = e.target.txtMarkdown.value;
    
    fetch('api/index.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            markdown: markdownCode,
        }),
    })
    .then((response) => response.text())
    .then((data) => {
        const sectionHTMLOutput = document.querySelector('#sectionOutput');
        sectionHTMLOutput.innerHTML = data;
        
        const sectionHTML = document.querySelector('#sectionHTML > pre');
        sectionHTML.innerText = data;

        sectionHTMLOutput.classList.add('visible');
        sectionHTML.parentElement.classList.add('visible');
    })
    .catch(error => console.error('Error:', error));
});