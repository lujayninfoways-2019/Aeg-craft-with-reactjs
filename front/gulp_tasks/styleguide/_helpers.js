/**
 * Strip away leftmost part of the path
 * @param  {String}       path  path to operate on
 * @param  {Integer}      steps how many slash separated parts to reduce
 * @return {String}       shortened path
 */
export function reducePathLeft(path, steps) {
  steps = steps-1;
  path = path.substring(path.indexOf('\\')+1 || path.indexOf('/')+1);
  if(steps > 0) return reducePathLeft(path, steps);
  return path;
}
