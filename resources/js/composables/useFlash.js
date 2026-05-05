export function useFlash(page) {
    let lastSuccess = null;
    let lastError = null;

    const showSuccess = (message) => {
        if (!message || message === lastSuccess || !window.Swal) return;

        lastSuccess = message;

        window.Swal.fire({
            icon: "success",
            title: "Berhasil",
            text: message,
            timer: 2200,
            showConfirmButton: false,
        });
    };

    const showError = (message) => {
        if (!message || message === lastError || !window.Swal) return;

        lastError = message;

        window.Swal.fire({
            icon: "error",
            title: "Gagal",
            text: message,
        });
    };

    return {
        showSuccess,
        showError,
    };
}
