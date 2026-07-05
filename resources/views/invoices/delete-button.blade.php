<form action="{{ route('invoices.destroy', $getRecord()->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
    @csrf
    @method('DELETE')
    <button type="submit" style="color: red; cursor: pointer; border: none; background: none;">
        🗑️ حذف
    </button>
</form>