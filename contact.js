document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("contactForm");

    form.addEventListener("submit", function (e) {
        e.preventDefault(); 

        const oldBox = document.getElementById("success-box");
        if (oldBox) oldBox.remove();

        const successBox = document.createElement("div");
        successBox.id = "success-box";
        successBox.textContent = "✅ Message sent successfully!";
        successBox.style.backgroundColor = "#d4edda";
        successBox.style.color = "#155724";
        successBox.style.border = "1px solid #c3e6cb";
        successBox.style.padding = "15px";
        successBox.style.borderRadius = "8px";
        successBox.style.marginTop = "15px";
        successBox.style.fontWeight = "bold";
        successBox.style.boxShadow = "0 2px 8px rgba(0,0,0,0.1)";

        form.parentNode.insertBefore(successBox, form.nextSibling);

        
        form.reset();

        
        setTimeout(() => {
            successBox.remove();
        }, 5000);
    });
});
