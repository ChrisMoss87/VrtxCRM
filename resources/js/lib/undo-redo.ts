/**
 * Undo/Redo system for tracking changes
 */

export interface HistoryState<T> {
	state: T;
	timestamp: number;
	description?: string;
}

export class UndoRedoManager<T> {
	private history: HistoryState<T>[] = [];
	private currentIndex: number = -1;
	private maxHistory: number;

	constructor(maxHistory: number = 50) {
		this.maxHistory = maxHistory;
	}

	/**
	 * Add a new state to history
	 */
	push(state: T, description?: string): void {
		// Remove any states after current index (if we've undone some changes)
		this.history = this.history.slice(0, this.currentIndex + 1);

		// Add new state
		this.history.push({
			state: JSON.parse(JSON.stringify(state)), // Deep clone
			timestamp: Date.now(),
			description,
		});

		// Limit history size
		if (this.history.length > this.maxHistory) {
			this.history.shift();
		} else {
			this.currentIndex++;
		}
	}

	/**
	 * Undo to previous state
	 */
	undo(): T | null {
		if (!this.canUndo()) {
			return null;
		}

		this.currentIndex--;
		return this.getCurrentState();
	}

	/**
	 * Redo to next state
	 */
	redo(): T | null {
		if (!this.canRedo()) {
			return null;
		}

		this.currentIndex++;
		return this.getCurrentState();
	}

	/**
	 * Check if undo is available
	 */
	canUndo(): boolean {
		return this.currentIndex > 0;
	}

	/**
	 * Check if redo is available
	 */
	canRedo(): boolean {
		return this.currentIndex < this.history.length - 1;
	}

	/**
	 * Get current state
	 */
	getCurrentState(): T | null {
		if (this.currentIndex < 0 || this.currentIndex >= this.history.length) {
			return null;
		}

		return JSON.parse(JSON.stringify(this.history[this.currentIndex].state));
	}

	/**
	 * Get history for display
	 */
	getHistory(): HistoryState<T>[] {
		return this.history.map((item) => ({
			...item,
			state: JSON.parse(JSON.stringify(item.state)),
		}));
	}

	/**
	 * Get current index
	 */
	getCurrentIndex(): number {
		return this.currentIndex;
	}

	/**
	 * Clear all history
	 */
	clear(): void {
		this.history = [];
		this.currentIndex = -1;
	}

	/**
	 * Get number of undoable changes
	 */
	getUndoCount(): number {
		return this.currentIndex;
	}

	/**
	 * Get number of redoable changes
	 */
	getRedoCount(): number {
		return this.history.length - this.currentIndex - 1;
	}
}
