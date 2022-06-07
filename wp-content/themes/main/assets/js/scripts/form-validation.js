/**
 * Last Edited: 01 September 2020
 * @author NguyenBa <banv.drei@gmail.com>
 * @method Validator
 * @param {*} options
 */
const Validator = (options) => {
  let selectorRules = {};
  const formElement = document.querySelector(options.form);

  const getParentElement = (element, selector) => {
    while (element.parentElement) {
      if (element.parentElement.matches(selector)) return element.parentElement;
      element = element.parentElement;
    }
  };
  const validate = (inputElement, rule) => {
    const errorElement = getParentElement(
      inputElement,
      options.formGroupSelector
    ).querySelector(options.errorSelector);
    const rules = selectorRules[rule.selector];
    let errorMessage;

    // lặp qua từng rule & kiểm tra
    // nếu có lỗi thì trả luôn về message
    for (let i = 0; i < rules.length; i++) {
      switch (inputElement.type) {
        case "radio":
        case "checkbox":
          if (!formElement.querySelector(rule.selector + ":checked")) {
            errorMessage = rules[i]("");
          } else {
            errorMessage = rules[i](
              formElement.querySelector(rule.selector + ":checked").value
            );
          }
          break;
        default:
          errorMessage = rules[i](inputElement.value);
      }
      if (errorMessage) break;
    }
    if (errorMessage) {
      errorElement.innerText = errorMessage;
      getParentElement(inputElement, options.formGroupSelector).classList.add(
        options.classError
      );
    } else {
      errorElement.innerText = "";
      getParentElement(
        inputElement,
        options.formGroupSelector
      ).classList.remove(options.classError);
    }

    return !errorMessage;
  };

  if (formElement) {
    const rules = options.rules;
    // lặp qua mỗi rule và xử lý
    rules.forEach((rule) => {
      // lưu lại các rule của input
      if (Array.isArray(selectorRules[rule.selector])) {
        selectorRules[rule.selector].push(rule.testing);
      } else {
        selectorRules[rule.selector] = [rule.testing];
      }
      const inputElements = formElement.querySelectorAll(rule.selector);
      Array.from(inputElements).forEach((inputElement) => {
        if (inputElement) {
          // xử lý trường hợp blur khỏi input
          inputElement.onblur = () => {
            validate(inputElement, rule);
          };

          // xử lý trường hợp người dùng nhập vào input
          inputElement.oninput = () => {
            const errorElement = getParentElement(
              inputElement,
              options.formGroupSelector
            ).querySelector(options.errorSelector);
            errorElement.innerText = "";
            getParentElement(
              inputElement,
              options.formGroupSelector
            ).classList.remove(options.classError);
          };
        }
      });
    });

    // lắng nghe xự kiện onsubmit
    formElement.onsubmit = (e) => {
      e.preventDefault();
      let isFormValid = true;
      rules.forEach((rule) => {
        const inputElement = formElement.querySelector(rule.selector);
        let isValid = validate(inputElement, rule);
        if (!isValid) isFormValid = false;
      });
      if (isFormValid) {
        if (typeof options.onSubmit === "function") {
          const enableInputs = formElement.querySelectorAll("[name]");
          const inputValues = Array.from(enableInputs).reduce(
            (values, input) => {
              switch (input.type) {
                case "radio":
                  values[input.name] = formElement.querySelector(
                    'input[name="' + input.name + '"]:checked'
                  ).value;
                  break;
                case "checkbox":
                  if (!input.matches(":checked")) {
                    values[input.name] = "";
                    return values;
                  }
                  if (!Array.isArray(values[input.name]))
                    values[input.name] = [];
                  values[input.name].push(input.value);
                  break;
                case "file":
                  values[input.name] = input.file;
                  break;
                default:
                  values[input.name] = input.value;
              }
              return values;
            },
            {}
          );
          options.onSubmit(inputValues);
        } else {
          formElement.submit();
        }
      }
    };
  }
};

Validator.isRequired = (selector, message) => {
  return {
    selector,
    testing: (value) =>
      value.trim() ? undefined : message || "Please enter this field.",
  };
};

Validator.isEmail = function (selector, message) {
  return {
    selector,
    testing: (value) => {
      const regex = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
      return regex.test(value) ? undefined : message || "Invalid email.";
    },
  };
};

Validator.isMinLength = (selector, min = 6, message) => {
  return {
    selector,
    testing: (value) =>
      value.length >= min
        ? undefined
        : message || `Please enter at least ${min} characters.`,
  };
};

Validator.isConfirmed = (selector, getConfirmValue, message) => {
  return {
    selector,
    testing: (value) =>
      value === getConfirmValue()
        ? undefined
        : message || "The password does not match.",
  };
};

Validator.isPhone = (selector, message) => {
  return {
    selector,
    testing: (value) => {
      const regex = /(03|07|08|09|01[2|6|8|9])+([0-9]{8})\b/;
      return regex.test(value) ? undefined : message || "Invalid phone number.";
    },
  };
};

Validator.isNumber = (selector, message) => {
  return {
    selector,
    testing: (value) => {
      const regex = /^[0-9]+$/;
      return regex.test(value)
        ? undefined
        : message || "This field can only be entered numeric characters.";
    },
  };
};

Validator.isText = (selector, message) => {
  return {
    selector,
    testing: (value) => {
      const regex =
        /^([a-zA-ZÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀỀỂưăạảấầẩẫậắằẳẵặẹẻẽềềểỄỆỈỊỌỎỐỒỔỖỘỚỜỞỠỢỤỦỨỪễệỉịọỏốồổỗộớờởỡợụủứừỬỮỰỲỴÝỶỸửữựỳỵỷỹ\s]+)$/i;
      return regex.test(value)
        ? undefined
        : message || "This field can only be entered text.";
    },
  };
};
