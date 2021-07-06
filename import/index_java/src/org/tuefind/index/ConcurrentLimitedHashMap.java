package org.tuefind.index;

import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.ConcurrentLinkedDeque;
import java.util.function.Function;

/**
 * Thread-safe cache for storing finished lookups to be re-used later.
 * - use computeIfAbsent to add elements if they do not exist & return the element (either generated or taken from cache)
 * - call cleanup to remove oldest elements, according to maxSize.
 */
public class ConcurrentLimitedHashMap<K extends Object, V extends Object> extends ConcurrentHashMap<K, V> {

    ConcurrentLinkedDeque<K> keyHistory = new ConcurrentLinkedDeque();

    protected int maxSize;

    /**
     * Generate a record only if the key does not yet exist.
     * Also store the added keys in a Deque, so we are able to
     * determine later what the oldest key is.
     */
    public V computeIfAbsent(K k, Function<? super K, ? extends V> fnctn) {
        // perform a synchronized check/add operation to avoid duplicate entries.
        synchronized (keyHistory) {
            if (!keyHistory.contains(k))
                keyHistory.addLast(k);
        }
        V v = super.computeIfAbsent(k, fnctn);
        cleanup();
        return v;
    }

    /**
     * Delete oldest entries, according to maxSize (see constructor).
     */
    protected synchronized void cleanup() {
        while (keyHistory.size() > maxSize) {
            keyHistory.removeFirst();
        }
    }

    public ConcurrentLimitedHashMap(final int maxSize) {
        this.maxSize = maxSize;
    }
}
