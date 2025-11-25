(function() {
    const DepartmentPage = {
        modal: null,
        openBtn: null,
        closeButtons: [],
        form: null,

        init() {
            this.modal = document.getElementById('addDepartmentModal');
            this.openBtn = document.getElementById('addDepartmentBtn');
            this.form = document.getElementById('addDepartmentForm');
            this.closeButtons = Array.from(document.querySelectorAll('[data-close="add-department"]'));

            if (this.openBtn && this.modal) {
                this.openBtn.addEventListener('click', () => this.openModal());
            }

            if (this.modal) {
                this.modal.addEventListener('click', (event) => {
                    if (event.target === this.modal) {
                        this.closeModal();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && this.isModalVisible()) {
                        this.closeModal();
                    }
                });
            }

            this.closeButtons.forEach((btn) => btn.addEventListener('click', () => this.closeModal()));

            if (this.form) {
                this.form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    this.submitForm();
                });
            }

            window.AddDepartmentModal = {
                open: () => this.openModal(),
                close: () => this.closeModal(),
            };
        },

        openModal() {
            if (!this.modal) return;
            this.modal.removeAttribute('hidden');
            this.modal.style.display = 'flex';
        },

        closeModal() {
            if (!this.modal) return;
            this.modal.setAttribute('hidden', 'hidden');
            this.modal.style.display = 'none';
            if (this.form) {
                this.form.reset();
            }
        },

        isModalVisible() {
            return this.modal && this.modal.style.display === 'flex';
        },

        async submitForm() {
            const submitBtn = document.getElementById('saveDepartmentBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            }

            try {
                const formData = new FormData(this.form);
                const payload = Object.fromEntries(formData.entries());

                const response = await fetch(`${window.PatientConfig?.baseUrl || ''}departments/create`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                const result = await response.json().catch(() => ({ status: 'error', message: 'Invalid response' }));

                if (response.ok && result.status === 'success') {
                    window.showDepartmentsNotification?.('Department created successfully', 'success');
                    this.closeModal();
                    window.location.reload();
                } else {
                    const message = result.message || 'Failed to create department';
                    window.showDepartmentsNotification?.(message, 'error');
                }
            } catch (error) {
                console.error('Failed to create department', error);
                window.showDepartmentsNotification?.('Server error while creating department', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Department';
                }
            }
        },
    };

    document.addEventListener('DOMContentLoaded', () => DepartmentPage.init());
})();
