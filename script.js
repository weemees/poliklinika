// Маска для телефона
document.addEventListener("DOMContentLoaded", function () {
  const phoneInputs = document.querySelectorAll('input[type="tel"]');

  phoneInputs.forEach((input) => {
    input.addEventListener("input", function (e) {
      let value = this.value.replace(/\D/g, "");

      if (value.length > 0) {
        value = "+7" + value.substring(1);
      }

      let formattedValue = "";

      if (value.length > 1) {
        formattedValue += value.substring(0, 2) + " ";

        if (value.length > 2) {
          formattedValue += "(" + value.substring(2, 5);

          if (value.length > 5) {
            formattedValue += ") " + value.substring(5, 8);

            if (value.length > 8) {
              formattedValue += "-" + value.substring(8, 10);

              if (value.length > 10) {
                formattedValue += "-" + value.substring(10, 12);
              }
            }
          }
        }
      }

      this.value = formattedValue;
    });
  });

  // Валидация форм
  const validateForm = (form) => {
    let isValid = true;
    form.querySelectorAll("[required]").forEach((field) => {
      if (!field.value) {
        field.style.borderColor = "#e74c3c";
        isValid = false;
      } else {
        field.style.borderColor = "#ddd";
      }
    });
    return isValid;
  };

  // Обработчики для форм
  document.querySelectorAll(".btn-primary").forEach((button) => {
    button.addEventListener("click", function (e) {
      const form = this.closest("form");
      if (form && !validateForm(form)) {
        e.preventDefault();
        alert("Пожалуйста, заполните все обязательные поля");
      }
    });
  });

  // Инициализация выбора даты (ограничение на прошлые даты)
  const dateInputs = document.querySelectorAll('input[type="date"]');
  dateInputs.forEach((input) => {
    if (!input.min) {
      input.min = new Date().toISOString().split("T")[0];
    }
  });
});
document.addEventListener("DOMContentLoaded", function () {
  // Подтверждение действий
  document.querySelectorAll('form[action*="delete"]').forEach((form) => {
    form.addEventListener("submit", function (e) {
      if (!confirm("Вы уверены, что хотите выполнить это действие?")) {
        e.preventDefault();
      }
    });
  });

  // Показ сообщений
  if (document.querySelector(".alert-success")) {
    setTimeout(() => {
      document.querySelector(".alert-success").style.display = "none";
    }, 5000);
  }
});
