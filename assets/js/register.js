document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('register-form')
    if ( ! form ) return

    form.addEventListener('submit', (event) => {
        const firstName = form.querySelector('[name="first_name"]').value.trim()
        const lastName = form.querySelector('[name="last_name"]').value.trim()
        const email = form.querySelector('[name="email"]').value.trim()
        const password = form.querySelector('[name="password"]').value
        const confirmPassword = form.querySelector('[name="confirm_password"]').value
        const terms = form.querySelector('[name="terms"]')

        const errors = [] // Store validation errors

        if ( ! firstName ) errors.push('First name is required.')
        if ( ! lastName ) errors.push('Last name is required.')
        if ( ! email ) {
            errors.push('Email is required.')
        } else {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
            if (!emailPattern.test(email)) {
                errors.push('Enter a valid email address.')
            }
        }

        if (password.length < 8) {
            errors.push('Password must be at least 8 characters long.')
        }

        if (password !== confirmPassword) {
            errors.push('Passwords do not match.')
        }

        if ( ! terms.checked) {
            errors.push('You must agree to the terms.')
        }

        let existingAlert = document.querySelector('.form-alert-error')
        if (existingAlert) {
            existingAlert.remove()
        }

        if (errors.length > 0) {
            event.preventDefault()

            const alertBox = document.createElement('div')
            alertBox.className = 'form-alert form-alert-error'
            alertBox.innerHTML = `<ul>${errors.map(error => `<li>${error}</li>`).join('')}</ul>`

            form.insertAdjacentElement('beforebegin', alertBox)
        }
    })
})